<?php

use App\Models\CmsContent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CmsContent::setVal('ai_agent_enabled', '1', 'ai_settings');
        CmsContent::setVal('feature_tenant_ai_agent', '1', 'platform_features');
    }

    public function down(): void
    {
    }
};
