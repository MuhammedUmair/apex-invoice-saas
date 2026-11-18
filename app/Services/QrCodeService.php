<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Enterprise-grade QR Code Generator.
 * Powered by chillerlan/php-qrcode (ISO/IEC 18004:2015 compliant).
 * Scans instantly with all iOS Camera, Android Camera, and commercial barcode readers.
 */
class QrCodeService
{
    /**
     * Render 100% compliant ISO/IEC 18004 SVG QR code string.
     */
    public static function svg(string $text, int $size = 140, int $margin = 3): string
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'addQuietzone' => true,
            'quietzoneSize' => $margin,
            'svgUseFillAttributes' => true,
        ]);

        return (new QRCode($options))->render($text);
    }

    /**
     * Render as Base64 Data URI SVG for clean inline embedding in <img> tags.
     */
    public static function dataUri(string $text, int $size = 140, int $margin = 3): string
    {
        $options = new QROptions([
            'version' => QRCode::VERSION_AUTO,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'addQuietzone' => true,
            'quietzoneSize' => $margin,
            'svgUseFillAttributes' => true,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($text);
    }
}
