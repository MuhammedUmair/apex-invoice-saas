<?php

namespace App\Services;

use App\Models\Client;

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
        'CA' => ['name' => 'GST/HST', 'rate' => 13.00, 'type' => 'gst_hst'],
        'IN' => ['name' => 'GST', 'rate' => 18.00, 'type' => 'gst'],
        'TR' => ['name' => 'KDV (VAT)', 'rate' => 20.00, 'type' => 'kdv'],
        'PK' => ['name' => 'Sales Tax / SST', 'rate' => 16.00, 'type' => 'sales_tax'],
    ];

    /**
     * Get tax information based on country code.
     */
    public function getPresetForCountry(string $countryCode): array
    {
        $code = strtoupper($countryCode);
        return $this->taxPresets[$code] ?? ['name' => 'VAT / Tax', 'rate' => 0.00, 'type' => 'custom'];
    }

    /**
     * Calculate financial totals with integer-cent accuracy.
     * Discount is deducted before tax calculations according to GAAP/IFRS standards.
     *
     * @param int $subtotalCents
     * @param int $discountCents
     * @param float $taxRate
     * @param Client|null $client
     * @return array
     */
    public function calculateTotals(
        int $subtotalCents,
        int $discountCents = 0,
        float $taxRate = 0.00,
        ?Client $client = null
    ): array {
        // Enforce non-negative bounds
        $subtotalCents = max(0, $subtotalCents);
        $discountCents = min($subtotalCents, max(0, $discountCents));
        
        $taxableAmountCents = $subtotalCents - $discountCents;

        // If client is tax-exempt, tax is always 0
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
