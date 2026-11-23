<?php

use App\Models\CmsContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_contents')) {
            CmsContent::updateOrCreate(
                ['key' => 'ai_agent_enabled'],
                ['value' => '1', 'group' => 'ai_settings']
            );
            CmsContent::updateOrCreate(
                ['key' => 'feature_tenant_ai_agent'],
                ['value' => '1', 'group' => 'platform_features']
            );
        }
    }

    public function down(): void
    {
    }
};
