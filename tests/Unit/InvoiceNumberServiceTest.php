<?php

namespace Tests\Unit;

use App\Models\Workspace;
use App\Services\InvoiceNumberService;
use Tests\TestCase;

class InvoiceNumberServiceTest extends TestCase
{
    private InvoiceNumberService $service;
    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceNumberService();
        $this->workspace = Workspace::create([
            'name' => 'Invoice Test Studio',
            'slug' => 'invoice-test-studio',
            'currency' => 'USD',
            'country_code' => 'US',
        ]);
    }

    public function test_generates_sequential_invoice_numbers(): void
    {
        $first = $this->service->generateNextNumber($this->workspace, 'INV', 2026);
        $second = $this->service->generateNextNumber($this->workspace, 'INV', 2026);
        $third = $this->service->generateNextNumber($this->workspace, 'INV', 2026);

        $this->assertEquals('INV-2026-0001', $first);
        $this->assertEquals('INV-2026-0002', $second);
        $this->assertEquals('INV-2026-0003', $third);
    }

    public function test_independent_sequences_for_different_prefixes(): void
    {
        $inv = $this->service->generateNextNumber($this->workspace, 'INV', 2026);
        $est = $this->service->generateNextNumber($this->workspace, 'EST', 2026);

        $this->assertEquals('INV-2026-0001', $inv);
        $this->assertEquals('EST-2026-0001', $est);
    }

    public function test_independent_sequences_for_different_years(): void
    {
        $y2025 = $this->service->generateNextNumber($this->workspace, 'INV', 2025);
        $y2026 = $this->service->generateNextNumber($this->workspace, 'INV', 2026);

        $this->assertEquals('INV-2025-0001', $y2025);
        $this->assertEquals('INV-2026-0001', $y2026);
    }

    public function test_generates_credit_note_number(): void
    {
        $cn = $this->service->generateNextCreditNoteNumber($this->workspace);
        $year = date('Y');
        $this->assertEquals("CN-{$year}-0001", $cn);
    }
}
