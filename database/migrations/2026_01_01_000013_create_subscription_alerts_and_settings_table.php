<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_alerts')) {
            Schema::create('subscription_alerts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type'); // signup, upgrade, downgrade, trial_expired, renewal, suspension
                $table->string('title');
                $table->text('message');
                $table->json('metadata')->nullable();
                $table->boolean('is_read')->default(false);
                $table->boolean('email_sent')->default(false);
                $table->timestamps();

                $table->index(['type', 'created_at']);
                $table->index('is_read');
            });
        }

        // Initialize default alert configuration in cms_contents if not exists
        $defaults = [
            'admin_alert_email' => 'billing@redmapleagency.com',
            'alert_on_signup' => '1',
            'alert_on_upgrade' => '1',
            'alert_on_downgrade' => '1',
            'alert_on_trial_expire' => '1',
            'trial_duration_days' => '14',
        ];

        foreach ($defaults as $k => $v) {
            if (!DB::table('cms_contents')->where('key', $k)->exists()) {
                DB::table('cms_contents')->insert([
                    'key' => $k,
                    'content' => $v,
                    'section' => 'subscription_alerts',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_alerts');
        DB::table('cms_contents')->where('section', 'subscription_alerts')->delete();
    }
};
