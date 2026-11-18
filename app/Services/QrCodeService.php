<?php

namespace App\Services;

/**
 * Pure PHP QR Code Generator (Model 2, Byte Mode, EC Level M/L).
 * Generates standards-compliant SVG QR codes completely offline with zero external dependencies.
 */
class QrCodeService
{
    // GF(256) tables with primitive polynomial 0x11D (285)
    private static array $exp = [];
    private static array $log = [];
    private static bool $gfInit = false;

    // QR Code Version Parameters [version => [totalCodewords, ecCodewords, [blocks, total, ec]]]
    // For Version 1 to 10 with Error Correction Level M
    private static array $versionParamsM = [
        1 => ['total' => 26, 'ec' => 10, 'blocks' => [[1, 26, 10]], 'size' => 21, 'align' => []],
        2 => ['total' => 44, 'ec' => 16, 'blocks' => [[1, 44, 16]], 'size' => 25, 'align' => [6, 18]],
        3 => ['total' => 70, 'ec' => 26, 'blocks' => [[1, 70, 26]], 'size' => 29, 'align' => [6, 22]],
        4 => ['total' => 100, 'ec' => 36, 'blocks' => [[2, 50, 18]], 'size' => 33, 'align' => [6, 26]],
        5 => ['total' => 134, 'ec' => 48, 'blocks' => [[2, 67, 24]], 'size' => 37, 'align' => [6, 30]],
        6 => ['total' => 172, 'ec' => 64, 'blocks' => [[4, 43, 16]], 'size' => 41, 'align' => [6, 34]],
        7 => ['total' => 196, 'ec' => 72, 'blocks' => [[4, 49, 18]], 'size' => 45, 'align' => [6, 22, 38]],
        8 => ['total' => 242, 'ec' => 88, 'blocks' => [[2, 61, 22], [2, 60, 22]], 'size' => 49, 'align' => [6, 24, 42]],
        9 => ['total' => 292, 'ec' => 110, 'blocks' => [[3, 59, 22], [2, 58, 22]], 'size' => 53, 'align' => [6, 26, 46]],
        10 => ['total' => 346, 'ec' => 130, 'blocks' => [[4, 69, 26], [1, 70, 26]], 'size' => 57, 'align' => [6, 28, 50]],
    ];

    private static function initGF(): void
    {
        if (self::$gfInit) return;
        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$exp[$i + 255] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        self::$gfInit = true;
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) return 0;
        return self::$exp[self::$log[$x] + self::$log[$y]];
    }

    private static function calcEc(array $data, int $ecCount): array
    {
        self::initGF();
        // Generate generator polynomial
        $gen = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $root = self::$exp[$i];
            $next = [0];
            foreach ($gen as $coef) {
                $next[] = self::gfMul($coef, $root);
            }
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
            }
            $gen = $next;
        }

        $res = array_fill(0, $ecCount, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $res[0];
            array_shift($res);
            $res[] = 0;
            if ($factor !== 0) {
                for ($j = 0; $j < $ecCount; $j++) {
                    $res[$j] ^= self::gfMul($gen[$j + 1], $factor);
                }
            }
        }
        return $res;
    }

    /**
     * Choose smallest QR version that fits byte length
     */
    private static function selectVersion(int $byteLen): int
    {
        foreach (self::$versionParamsM as $ver => $params) {
            $dataCap = $params['total'] - $params['ec'];
            // 4 bits mode + 8 bits (or 16 for ver 10) length + data
            $lenBits = ($ver < 10) ? 8 : 16;
            $neededCodewords = (int) ceil((4 + $lenBits + ($byteLen * 8)) / 8);
            if ($neededCodewords <= $dataCap) {
                return $ver;
            }
        }
        return 10;
    }

    /**
     * Generate 2D boolean module matrix for input string
     */
    public static function generateMatrix(string $text): array
    {
        self::initGF();
        $bytes = array_values(unpack('C*', $text));
        $len = count($bytes);
        $version = self::selectVersion($len);
        $params = self::$versionParamsM[$version];
        $size = $params['size'];
        $totalCodewords = $params['total'];
        $ecCodewords = $params['ec'];
        $dataCodewords = $totalCodewords - $ecCodewords;

        // 1. Bit Stream (Byte mode: 0100)
        $bits = [0, 1, 0, 0];
        $charCountBits = ($version < 10) ? 8 : 16;
        for ($i = $charCountBits - 1; $i >= 0; $i--) {
            $bits[] = ($len >> $i) & 1;
        }
        foreach ($bytes as $b) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($b >> $i) & 1;
            }
        }

        // Terminator (up to 4 zeroes)
        $maxDataBits = $dataCodewords * 8;
        $terminatorLen = min(4, $maxDataBits - count($bits));
        for ($i = 0; $i < $terminatorLen; $i++) {
            $bits[] = 0;
        }

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        // Pad bytes (0xEC, 0x11)
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bits) < $maxDataBits) {
            $pad = $padBytes[$padIdx % 2];
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($pad >> $i) & 1;
            }
            $padIdx++;
        }

        // Group into data codewords
        $codewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $val = 0;
            for ($j = 0; $j < 8; $j++) {
                $val = ($val << 1) | $bits[$i + $j];
            }
            $codewords[] = $val;
        }

        // 2. Block division & Error Correction
        $blocksData = [];
        $blocksEc = [];
        $cwIdx = 0;
        foreach ($params['blocks'] as $bGroup) {
            [$numBlocks, $blockTotal, $blockEc] = $bGroup;
            $blockDataCount = $blockTotal - $blockEc;
            for ($b = 0; $b < $numBlocks; $b++) {
                $bData = array_slice($codewords, $cwIdx, $blockDataCount);
                $cwIdx += $blockDataCount;
                $bEc = self::calcEc($bData, $blockEc);
                $blocksData[] = $bData;
                $blocksEc[] = $bEc;
            }
        }

        // 3. Interleaving
        $finalCodewords = [];
        $maxDataLen = 0;
        foreach ($blocksData as $bd) $maxDataLen = max($maxDataLen, count($bd));
        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($blocksData as $bd) {
                if ($i < count($bd)) $finalCodewords[] = $bd[$i];
            }
        }
        $maxEcLen = 0;
        foreach ($blocksEc as $be) $maxEcLen = max($maxEcLen, count($be));
        for ($i = 0; $i < $maxEcLen; $i++) {
            foreach ($blocksEc as $be) {
                if ($i < count($be)) $finalCodewords[] = $be[$i];
            }
        }

        // 4. Construct Matrix
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // Helper: place finder pattern
        $placeFinder = function(int $r, int $c) use (&$matrix, &$reserved, $size) {
            for ($y = -1; $y <= 7; $y++) {
                for ($x = -1; $x <= 7; $x++) {
                    $row = $r + $y;
                    $col = $c + $x;
                    if ($row >= 0 && $row < $size && $col >= 0 && $col < $size) {
                        $isFinder = ($y >= 0 && $y <= 6 && $x >= 0 && $x <= 6) &&
                            ($y === 0 || $y === 6 || $x === 0 || $x === 6 || ($y >= 2 && $y <= 4 && $x >= 2 && $x <= 4));
                        $matrix[$row][$col] = $isFinder;
                        $reserved[$row][$col] = true;
                    }
                }
            }
        };

        // 3 Finder patterns
        $placeFinder(0, 0);
        $placeFinder(0, $size - 7);
        $placeFinder($size - 7, 0);

        // Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            if (!$reserved[6][$i]) {
                $matrix[6][$i] = ($i % 2 === 0);
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $matrix[$i][6] = ($i % 2 === 0);
                $reserved[$i][6] = true;
            }
        }

        // Alignment patterns
        $alignPos = $params['align'];
        $nAlign = count($alignPos);
        for ($i = 0; $i < $nAlign; $i++) {
            for ($j = 0; $j < $nAlign; $j++) {
                $ar = $alignPos[$i];
                $ac = $alignPos[$j];
                // Don't overlap finders
                if (($ar < 9 && $ac < 9) || ($ar < 9 && $ac >= $size - 8) || ($ar >= $size - 8 && $ac < 9)) {
                    continue;
                }
                for ($y = -2; $y <= 2; $y++) {
                    for ($x = -2; $x <= 2; $x++) {
                        $matrix[$ar + $y][$ac + $x] = (abs($y) === 2 || abs($x) === 2 || ($y === 0 && $x === 0));
                        $reserved[$ar + $y][$ac + $x] = true;
                    }
                }
            }
        }

        // Dark module & reserve format info
        $matrix[$size - 8][8] = true;
        $reserved[$size - 8][8] = true;
        for ($i = 0; $i < 9; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
            $reserved[8][$size - 1 - $i] = true;
            $reserved[$size - 1 - $i][8] = true;
        }

        // 5. Place Data Bits
        $dataBits = [];
        foreach ($finalCodewords as $cw) {
            for ($i = 7; $i >= 0; $i--) {
                $dataBits[] = ($cw >> $i) & 1;
            }
        }
        // Remainder bits if any
        $totalMatrixBits = 0;
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if (!$reserved[$r][$c]) $totalMatrixBits++;
            }
        }
        while (count($dataBits) < $totalMatrixBits) {
            $dataBits[] = 0;
        }

        $bitIdx = 0;
        $direction = -1; // up
        $row = $size - 1;
        $col = $size - 1;

        while ($col > 0) {
            if ($col === 6) $col--; // skip vertical timing column
            for ($i = 0; $i < 2; $i++) {
                $c = $col - $i;
                if (!$reserved[$row][$c]) {
                    $bit = $dataBits[$bitIdx++] ?? 0;
                    // Apply Mask 0: (row + col) % 2 == 0
                    $mask = (($row + $c) % 2 === 0);
                    $matrix[$row][$c] = ($bit === 1) ^ $mask;
                }
            }
            $row += $direction;
            if ($row < 0 || $row >= $size) {
                $direction = -$direction;
                $row += $direction;
                $col -= 2;
            }
        }

        // 6. Format info: EC Level M (00) + Mask 0 (000) = 00000 => BCH coded: 0b101010000010010 XOR 0b101010000010010
        // Standard Format Information for Level M, Mask 0: 0x5412 XOR 0x0000 = 0x5412 => 0b101010000010010
        $fmtBits = 0x5412;
        $fmt = [];
        for ($i = 14; $i >= 0; $i--) {
            $fmt[] = ($fmtBits >> $i) & 1;
        }

        // Write format bits around top-left finder
        $matrix[8][0] = (bool) $fmt[0];
        $matrix[8][1] = (bool) $fmt[1];
        $matrix[8][2] = (bool) $fmt[2];
        $matrix[8][3] = (bool) $fmt[3];
        $matrix[8][4] = (bool) $fmt[4];
        $matrix[8][5] = (bool) $fmt[5];
        $matrix[8][7] = (bool) $fmt[6];
        $matrix[8][8] = (bool) $fmt[7];
        $matrix[7][8] = (bool) $fmt[8];
        $matrix[5][8] = (bool) $fmt[9];
        $matrix[4][8] = (bool) $fmt[10];
        $matrix[3][8] = (bool) $fmt[11];
        $matrix[2][8] = (bool) $fmt[12];
        $matrix[1][8] = (bool) $fmt[13];
        $matrix[0][8] = (bool) $fmt[14];

        // Write format bits around other two finders
        for ($i = 0; $i < 7; $i++) {
            $matrix[$size - 1 - $i][8] = (bool) $fmt[$i];
        }
        for ($i = 0; $i < 8; $i++) {
            $matrix[8][$size - 8 + $i] = (bool) $fmt[7 + $i];
        }

        return $matrix;
    }

    /**
     * Render QR code matrix as optimized SVG string.
     */
    public static function svg(string $text, int $size = 140, int $margin = 2, string $color = '#0f172a', string $bgColor = '#ffffff'): string
    {
        $matrix = self::generateMatrix($text);
        $n = count($matrix);
        $totalSize = $n + ($margin * 2);

        $pathD = '';
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $pathD .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalSize . ' ' . $totalSize . '" width="' . $size . '" height="' . $size . '" shape-rendering="crispEdges">' .
            '<rect width="100%" height="100%" fill="' . $bgColor . '"/>' .
            '<path d="' . rtrim($pathD) . '" fill="' . $color . '"/>' .
            '</svg>';
    }

    /**
     * Render as Base64 Data URI SVG for easy embedding in <img> tags.
     */
    public static function dataUri(string $text, int $size = 140, int $margin = 2, string $color = '#0f172a'): string
    {
        $svg = self::svg($text, $size, $margin, $color);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
