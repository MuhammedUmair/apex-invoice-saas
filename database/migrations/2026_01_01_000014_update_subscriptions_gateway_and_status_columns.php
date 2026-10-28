<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify gateway and status from strict enum to VARCHAR(50)
        // to support manual_admin, bank, suspended, inactive, etc.
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `gateway` VARCHAR(50) NOT NULL DEFAULT 'trial'");
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'trialing'");
    }

    public function down(): void
    {
        // Revert to enum if ever rolled back
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `gateway` ENUM('stripe', 'paypal', 'trial') NOT NULL DEFAULT 'trial'");
        DB::statement("ALTER TABLE `subscriptions` MODIFY COLUMN `status` ENUM('trialing', 'active', 'past_due', 'canceled', 'expired') NOT NULL DEFAULT 'trialing'");
    }
};
