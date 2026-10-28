<?php

namespace App\Console\Commands;

use App\Services\SubscriptionAlertService;
use Illuminate\Console\Command;

class CheckSubscriptionExpirations extends Command
{
    protected $signature = 'subscriptions:check-expirations';
    protected $description = 'Scan for expired trials and past-due subscriptions, update status, and dispatch admin alerts.';

    public function handle(SubscriptionAlertService $alertService): int
    {
        $this->info('Scanning for expired trials...');
        $count = $alertService->checkExpiredTrials();
        $this->info("Processed {$count} expired trial(s) and dispatched admin notification(s).");

        return Command::SUCCESS;
    }
}
