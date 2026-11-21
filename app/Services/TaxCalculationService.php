<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Workspace;

class TaxCalculationService
{
    /**
     * Standard country tax presets
     */
    protected array $taxPresets = [
        'US' => ['name' => 'Sales Tax', 'rate' => 0.00, 'type' => 'sales_tax'],
        'GB' => ['name' => 'VAT', 'rate' => 20.00, 'type' => 'vat'],
        'DE' => ['name' => 'MwSt (VAT)', 'rate' => 19.00, 'type' => 'vat'],
        'FR' => ['name' => 'TVA (VAT)', 'rate' => 20.00, 'type' => 'vat'],
        'ES' => ['name' => 'IVA (VAT)', 'rate' => 21.00, 'type' => 'vat'],
        'AE' => ['name' => 'VAT', 'rate' => 5.00, 'type' => 'vat'],
        'SA' => ['name' => 'VAT (ZATCA)', 'rate' => 15.00, 'type' => 'vat'],
        'AU' => ['name' => 'GST', 'rate' => 10.00, 'type' => 'gst'],
        'CA' => ['name' => 'Dynamic Place-of-Supply', 'rate' => 0.00, 'type' => 'place_of_supply'],
        'IN' => ['name' => 'GST', 'rate' => 18.00, 'type' => 'gst'],
        'TR' => ['name' => 'KDV (VAT)', 'rate' => 20.00, 'type' => 'kdv'],
        'PK' => ['name' => 'Sales Tax / SST', 'rate' => 16.00, 'type' => 'sales_tax'],
    ];

    /**
     * Canadian province/territory normalization map.
     */
    protected static array $canadianProvinces = [
        'ON' => 'ON',
        'ONTARIO' => 'ON',
        'QC' => 'QC',
        'QUEBEC' => 'QC',
        'QUÉBEC' => 'QC',
        'BC' => 'BC',
        'BRITISH COLUMBIA' => 'BC',
        'AB' => 'AB',
        'ALBERTA' => 'AB',
        'SK' => 'SK',
        'SASKATCHEWAN' => 'SK',
        'MB' => 'MB',
        'MANITOBA' => 'MB',
        'NS' => 'NS',
        'NOVA SCOTIA' => 'NS',
        'NB' => 'NB',
        'NEW BRUNSWICK' => 'NB',
        'NL' => 'NL',
        'NEWFOUNDLAND' => 'NL',
        'NEWFOUNDLAND AND LABRADOR' => 'NL',
        'PE' => 'PE',
        'PEI' => 'PE',
        'PRINCE EDWARD ISLAND' => 'PE',
        'NT' => 'NT',
        'NORTHWEST TERRITORIES' => 'NT',
        'NU' => 'NU',
        'NUNAVUT' => 'NU',
        'YT' => 'YT',
        'YUKON' => 'YT',
        'YUKON TERRITORY' => 'YT',
    ];

    /**
     * Normalize a province name or code into its official 2-letter Canadian code.
     */
    public static function normalizeCanadianProvince(?string $province): ?string
    {
        if (empty($province)) {
            return null;
        }

        $upper = mb_strtoupper(trim($province), 'UTF-8');
        if (isset(self::$canadianProvinces[$upper])) {
            return self::$canadianProvinces[$upper];
        }

        // Also normalize accents (e.g. É -> E)
        $unaccented = str_replace(['É', 'È', 'Ê', 'Ë', 'Â', 'À'], ['E', 'E', 'E', 'E', 'A', 'A'], $upper);
        return self::$canadianProvinces[$unaccented] ?? null;
    }

    /**
     * Get official Canadian tax details and breakdown for a given province code.
     */
    public static function getCanadianProvinceDetails(string $rawProvince, bool $collectQst = true, bool $collectPst = true): array
    {
        $code = self::normalizeCanadianProvince($rawProvince) ?: 'ON';

        $federalTaxName = 'GST';
        $federalTaxRate = 5.000;
        $provincialTaxName = null;
        $provincialTaxRate = 0.000;
        $isDualTax = false;
        $name = 'Ontario';
        $label = 'HST (13.00%)';

        switch ($code) {
            case 'AB':
                $name = 'Alberta';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = '5.00% GST (Federal GST Only)';
                break;
            case 'NT':
                $name = 'Northwest Territories';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = '5.00% GST (Territorial GST Only)';
                break;
            case 'NU':
                $name = 'Nunavut';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = '5.00% GST (Territorial GST Only)';
                break;
            case 'YT':
                $name = 'Yukon';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = '5.00% GST (Territorial GST Only)';
                break;

            case 'ON':
                $name = 'Ontario';
                $federalTaxName = 'HST';
                $federalTaxRate = 13.000;
                $label = '13.00% HST (Harmonized Sales Tax)';
                break;

            case 'NS':
                $name = 'Nova Scotia';
                $federalTaxName = 'HST';
                $federalTaxRate = 14.000;
                $label = '14.00% HST (Harmonized Sales Tax)';
                break;

            case 'NB':
                $name = 'New Brunswick';
                $federalTaxName = 'HST';
                $federalTaxRate = 15.000;
                $label = '15.00% HST (Harmonized Sales Tax)';
                break;
            case 'NL':
                $name = 'Newfoundland and Labrador';
                $federalTaxName = 'HST';
                $federalTaxRate = 15.000;
                $label = '15.00% HST (Harmonized Sales Tax)';
                break;
            case 'PE':
                $name = 'Prince Edward Island';
                $federalTaxName = 'HST';
                $federalTaxRate = 15.000;
                $label = '15.00% HST (Harmonized Sales Tax)';
                break;

            case 'QC':
                $name = 'Quebec';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectQst) {
                    $provincialTaxName = 'QST';
                    $provincialTaxRate = 9.975;
                    $isDualTax = true;
                    $label = '14.975% (5% Federal GST + 9.975% Revenu Québec QST)';
                } else {
                    $label = '5.00% GST';
                }
                break;

            case 'BC':
                $name = 'British Columbia';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectPst) {
                    $provincialTaxName = 'PST';
                    $provincialTaxRate = 7.000;
                    $isDualTax = true;
                    $label = '12.00% (5% Federal GST + 7% BC PST)';
                } else {
                    $label = '5.00% GST';
                }
                break;
            case 'MB':
                $name = 'Manitoba';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectPst) {
                    $provincialTaxName = 'PST';
                    $provincialTaxRate = 7.000;
                    $isDualTax = true;
                    $label = '12.00% (5% Federal GST + 7% Manitoba PST)';
                } else {
                    $label = '5.00% GST';
                }
                break;

            case 'SK':
                $name = 'Saskatchewan';
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectPst) {
                    $provincialTaxName = 'PST';
                    $provincialTaxRate = 6.000;
                    $isDualTax = true;
                    $label = '11.00% (5% Federal GST + 6% Saskatchewan PST)';
                } else {
                    $label = '5.00% GST';
                }
                break;
        }

        $totalRate = $federalTaxRate + $provincialTaxRate;

        return [
            'code' => $code,
            'name' => $name,
            'federal_tax_name' => $federalTaxName,
            'federal_tax_rate' => $federalTaxRate,
            'provincial_tax_name' => $provincialTaxName,
            'provincial_tax_rate' => $provincialTaxRate,
            'total_rate' => $totalRate,
            'is_dual_tax' => $isDualTax,
            'label' => $label,
        ];
    }
    public function getPresetForCountry(string $countryCode): array
    {
        $code = strtoupper($countryCode);
        return $this->taxPresets[$code] ?? ['name' => 'VAT / Tax', 'rate' => 0.00, 'type' => 'custom'];
    }

    /**
     * Calculate Canadian Place-of-Supply or international tax for a specific workspace and client.
     */
    public function calculateForClient(
        ?Workspace $workspace,
        ?Client $client,
        int $subtotalCents,
        int $discountCents = 0,
        float $fallbackTaxRate = 0.00,
        string $fallbackTaxName = 'VAT',
        ?string $overrideProvince = null
    ): array {
        $subtotalCents = max(0, $subtotalCents);
        $discountCents = min($subtotalCents, max(0, $discountCents));
        $taxableAmountCents = $subtotalCents - $discountCents;

        $isCanadianWorkspace = $workspace && $workspace->isCanadianWorkspace();

        // 1. Client Tax-Exempt Override
        if ($client && $client->is_tax_exempt) {
            return $this->buildTaxResult(
                $subtotalCents,
                $discountCents,
                $taxableAmountCents,
                'Tax Exempt',
                0.00,
                0,
                null,
                0.00,
                0,
                false,
                'Client is marked as Tax Exempt'
            );
        }

        // 2. Canadian Workspace Place-of-Supply Rules
        if ($isCanadianWorkspace) {
            // Small-Supplier Exemption Rule (<$30,000 CAD)
            if (!empty($workspace->small_supplier_exempt)) {
                return $this->buildTaxResult(
                    $subtotalCents,
                    $discountCents,
                    $taxableAmountCents,
                    'Zero-Rated (Small Supplier)',
                    0.00,
                    0,
                    null,
                    0.00,
                    0,
                    false,
                    'CRA Small-Supplier Exempt (0% Tax)'
                );
            }

            $clientCountry = strtoupper($client->country_code ?? 'CA');

            // Non-Canadian Client = Zero-Rated Export
            if ($client && $clientCountry !== 'CA') {
                return $this->buildTaxResult(
                    $subtotalCents,
                    $discountCents,
                    $taxableAmountCents,
                    'Zero-Rated Export',
                    0.00,
                    0,
                    null,
                    0.00,
                    0,
                    false,
                    'Export Outside Canada (0% Zero-Rated)'
                );
            }

            // Client resides in Canada: evaluate Place-of-Supply province (override > client > workspace province)
            $rawProvince = $overrideProvince ?: ($client?->province ?? $client?->state ?? $workspace->province ?? 'ON');
            $provinceCode = self::normalizeCanadianProvince($rawProvince) ?: 'ON';

            return $this->calculateCanadianPlaceOfSupply(
                $workspace,
                $provinceCode,
                $subtotalCents,
                $discountCents,
                $taxableAmountCents
            );
        }

        // 3. Non-Canadian Workspaces: Fallback to standard single tax calculation
        $rate = max(0.00, $fallbackTaxRate);
        $taxCents = (int) round(($taxableAmountCents * $rate) / 100);

        return $this->buildTaxResult(
            $subtotalCents,
            $discountCents,
            $taxableAmountCents,
            $fallbackTaxName,
            $rate,
            $taxCents,
            null,
            0.00,
            0,
            false,
            null
        );
    }

    /**
     * Compute specific Canadian provincial place-of-supply breakdown.
     */
    protected function calculateCanadianPlaceOfSupply(
        Workspace $workspace,
        string $provinceCode,
        int $subtotalCents,
        int $discountCents,
        int $taxableAmountCents
    ): array {
        $collectQst = $workspace->collect_qst ?? true;
        $collectPst = $workspace->collect_pst ?? true;

        $federalTaxName = 'GST';
        $federalTaxRate = 5.000;
        $provincialTaxName = null;
        $provincialTaxRate = 0.000;
        $isDualTax = false;
        $label = 'GST (5.00%)';

        switch ($provinceCode) {
            case 'AB':
            case 'NT':
            case 'NU':
            case 'YT':
                // GST only (5.000%)
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = 'GST (5.00%)';
                break;

            case 'ON':
                // Ontario HST (13.000%)
                $federalTaxName = 'HST';
                $federalTaxRate = 13.000;
                $label = 'HST (13.00%)';
                break;

            case 'NS':
                // Nova Scotia HST (14.000%)
                $federalTaxName = 'HST';
                $federalTaxRate = 14.000;
                $label = 'HST (14.00%)';
                break;

            case 'NB':
            case 'NL':
            case 'PE':
                // Atlantic 15.000% HST
                $federalTaxName = 'HST';
                $federalTaxRate = 15.000;
                $label = 'HST (15.00%)';
                break;

            case 'QC':
                // Quebec: 5.000% GST + 9.975% QST (non-compounded)
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectQst) {
                    $provincialTaxName = 'QST';
                    $provincialTaxRate = 9.975;
                    $isDualTax = true;
                    $label = 'GST (5.00%) + QST (9.975%)';
                } else {
                    $label = 'GST (5.00%)';
                }
                break;

            case 'BC':
            case 'MB':
                // BC & MB: 5.000% GST + 7.000% PST (non-compounded)
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectPst) {
                    $provincialTaxName = 'PST';
                    $provincialTaxRate = 7.000;
                    $isDualTax = true;
                    $label = "GST (5.00%) + PST (7.00%)";
                } else {
                    $label = 'GST (5.00%)';
                }
                break;

            case 'SK':
                // Saskatchewan: 5.000% GST + 6.000% PST (non-compounded)
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                if ($collectPst) {
                    $provincialTaxName = 'PST';
                    $provincialTaxRate = 6.000;
                    $isDualTax = true;
                    $label = 'GST (5.00%) + PST (6.00%)';
                } else {
                    $label = 'GST (5.00%)';
                }
                break;

            default:
                // Fallback for unknown Canadian territory
                $federalTaxName = 'GST';
                $federalTaxRate = 5.000;
                $label = 'GST (5.00%)';
                break;
        }

        $federalTaxCents = (int) round(($taxableAmountCents * $federalTaxRate) / 100);
        $provincialTaxCents = $isDualTax ? (int) round(($taxableAmountCents * $provincialTaxRate) / 100) : 0;

        $provinceNames = [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NL' => 'Newfoundland and Labrador',
            'NS' => 'Nova Scotia',
            'NT' => 'Northwest Territories',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon',
        ];
        $provinceName = $provinceNames[$provinceCode] ?? $provinceCode;

        return $this->buildTaxResult(
            $subtotalCents,
            $discountCents,
            $taxableAmountCents,
            $federalTaxName,
            $federalTaxRate,
            $federalTaxCents,
            $provincialTaxName,
            $provincialTaxRate,
            $provincialTaxCents,
            $isDualTax,
            "Place of Supply: {$provinceName} ({$label})"
        );
    }

    /**
     * Standardized result builder for integers and decimal values.
     */
    protected function buildTaxResult(
        int $subtotalCents,
        int $discountCents,
        int $taxableCents,
        string $federalTaxName,
        float $federalTaxRate,
        int $federalTaxCents,
        ?string $provincialTaxName,
        float $provincialTaxRate,
        int $provincialTaxCents,
        bool $isDualTax,
        ?string $note
    ): array {
        $totalTaxCents = $federalTaxCents + $provincialTaxCents;
        $combinedTaxRate = $federalTaxRate + $provincialTaxRate;
        $totalCents = $taxableCents + $totalTaxCents;

        $displayName = $isDualTax
            ? "{$federalTaxName} + {$provincialTaxName}"
            : $federalTaxName;

        return [
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'taxable_cents' => $taxableCents,
            'tax_name' => $displayName,
            'tax_rate' => $combinedTaxRate,
            'tax_cents' => $totalTaxCents,
            'federal_tax_name' => $federalTaxName,
            'federal_tax_rate' => $federalTaxRate,
            'federal_tax_cents' => $federalTaxCents,
            'provincial_tax_name' => $provincialTaxName,
            'provincial_tax_rate' => $provincialTaxRate,
            'provincial_tax_cents' => $provincialTaxCents,
            'is_dual_tax' => $isDualTax,
            'total_cents' => $totalCents,
            'note' => $note,
        ];
    }

    /**
     * Calculate financial totals with integer-cent accuracy (Legacy & Generic adapter).
     */
    public function calculateTotals(
        int $subtotalCents,
        int $discountCents = 0,
        float $taxRate = 0.00,
        ?Client $client = null
    ): array {
        $subtotalCents = max(0, $subtotalCents);
        $discountCents = min($subtotalCents, max(0, $discountCents));
        $taxableAmountCents = $subtotalCents - $discountCents;

        if ($client && $client->is_tax_exempt) {
            $effectiveTaxRate = 0.00;
            $taxCents = 0;
        } else {
            $effectiveTaxRate = max(0.00, $taxRate);
            $taxCents = (int) round(($taxableAmountCents * $effectiveTaxRate) / 100);
        }

        $totalCents = $taxableAmountCents + $taxCents;

        return [
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'taxable_cents' => $taxableAmountCents,
            'tax_rate' => $effectiveTaxRate,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
        ];
    }
}
