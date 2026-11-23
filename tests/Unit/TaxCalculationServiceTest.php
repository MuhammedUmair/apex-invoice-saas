<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Workspace;
use App\Services\TaxCalculationService;
use Tests\TestCase;

class TaxCalculationServiceTest extends TestCase
{
    private TaxCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaxCalculationService();
    }

    public function test_get_preset_for_supported_countries(): void
    {
        $gb = $this->service->getPresetForCountry('GB');
        $this->assertEquals('VAT', $gb['name']);
        $this->assertEquals(20.00, $gb['rate']);

        $ae = $this->service->getPresetForCountry('AE');
        $this->assertEquals('VAT', $ae['name']);
        $this->assertEquals(5.00, $ae['rate']);

        $sa = $this->service->getPresetForCountry('SA');
        $this->assertEquals('VAT (ZATCA)', $sa['name']);
        $this->assertEquals(15.00, $sa['rate']);
    }

    public function test_normalize_canadian_provinces(): void
    {
        $this->assertEquals('ON', TaxCalculationService::normalizeCanadianProvince('Ontario'));
        $this->assertEquals('ON', TaxCalculationService::normalizeCanadianProvince('on'));
        $this->assertEquals('QC', TaxCalculationService::normalizeCanadianProvince('Quebec'));
        $this->assertEquals('QC', TaxCalculationService::normalizeCanadianProvince('Québec'));
        $this->assertEquals('BC', TaxCalculationService::normalizeCanadianProvince('British Columbia'));
        $this->assertEquals('AB', TaxCalculationService::normalizeCanadianProvince('Alberta'));
        $this->assertNull(TaxCalculationService::normalizeCanadianProvince(null));
    }

    public function test_canadian_province_tax_rates(): void
    {
        $on = TaxCalculationService::getCanadianProvinceDetails('ON');
        $this->assertEquals(13.00, $on['total_rate']);
        $this->assertEquals('HST', $on['federal_tax_name']);
        $this->assertFalse($on['is_dual_tax']);

        $qc = TaxCalculationService::getCanadianProvinceDetails('QC');
        $this->assertEquals(14.975, $qc['total_rate']);
        $this->assertEquals('GST', $qc['federal_tax_name']);
        $this->assertEquals('QST', $qc['provincial_tax_name']);
        $this->assertTrue($qc['is_dual_tax']);
    }

    public function test_tax_calculation_for_tax_exempt_client(): void
    {
        $workspace = Workspace::create([
            'name' => 'Canada Corp',
            'slug' => 'canada-corp',
            'currency' => 'CAD',
            'country_code' => 'CA',
        ]);

        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name' => 'Tax Exempt Org',
            'email' => 'exempt@org.test',
            'is_tax_exempt' => true,
        ]);

        $result = $this->service->calculateForClient(
            $workspace,
            $client,
            subtotalCents: 10000, // $100.00
            discountCents: 0
        );

        $this->assertEquals(0, $result['tax_amount_cents']);
        $this->assertEquals(10000, $result['total_cents']);
        $this->assertEquals('Tax Exempt', $result['tax_name']);
    }
}
