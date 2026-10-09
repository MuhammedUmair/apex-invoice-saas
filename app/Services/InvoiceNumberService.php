<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Atomically generate the next sequential invoice number for a workspace.
     * Uses pessimistic locking (SELECT ... FOR UPDATE) to guarantee gapless, race-condition-free sequences.
     */
    public function generateNextNumber(Workspace $workspace, string $prefix = 'INV', ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');

        return DB::transaction(function () use ($workspace, $prefix, $year) {
            $sequence = InvoiceSequence::where('workspace_id', $workspace->id)
                ->where('prefix', $prefix)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = InvoiceSequence::create([
                    'workspace_id' => $workspace->id,
                    'prefix' => $prefix,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $sequence->last_number += 1;
            $sequence->save();

            $paddedNumber = str_pad((string)$sequence->last_number, 4, '0', STR_PAD_LEFT);
            return "{$prefix}-{$year}-{$paddedNumber}";
        });
    }

    /**
     * Atomically generate next credit note number.
     */
    public function generateNextCreditNoteNumber(Workspace $workspace): string
    {
        return $this->generateNextNumber($workspace, 'CN');
    }

    /**
     * Atomically generate next payment receipt number.
     */
    public function generateNextPaymentNumber(Workspace $workspace): string
    {
        return $this->generateNextNumber($workspace, 'REC');
    }
}
