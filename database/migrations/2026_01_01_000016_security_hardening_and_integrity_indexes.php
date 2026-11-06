<?php

use App\Models\IndustryPreset;
use App\Models\PresetItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Audit Logs Table (Item 9)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 100);
                $table->string('auditable_type', 150)->nullable();
                $table->string('auditable_id', 100)->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['workspace_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['auditable_type', 'auditable_id']);
            });
        }

        // 2. Expand Invoices with Portal Security & Token Lockout (Item 6 & 8)
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'portal_token_expires_at')) {
                $table->timestamp('portal_token_expires_at')->nullable()->after('public_secure_token');
            }
            if (!Schema::hasColumn('invoices', 'failed_pin_attempts')) {
                $table->unsignedTinyInteger('failed_pin_attempts')->default(0)->after('portal_token_expires_at');
            }
            if (!Schema::hasColumn('invoices', 'pin_locked_until')) {
                $table->timestamp('pin_locked_until')->nullable()->after('failed_pin_attempts');
            }
        });

        // 3. Expand Payments with Unique Payment Number & Idempotency Key (Item 8)
        Schema::table('payments', function (Blueprint $table) {
            // Ensure unique workspace payment number
            $table->unique(['workspace_id', 'payment_number'], 'unique_workspace_payment_number');
        });

        // 4. Expand Workspaces with Daily Email Send Counters (Item 4)
        Schema::table('workspaces', function (Blueprint $table) {
            if (!Schema::hasColumn('workspaces', 'daily_email_send_cap')) {
                $table->unsignedInteger('daily_email_send_cap')->default(5)->after('is_whitelabel_enabled');
            }
            if (!Schema::hasColumn('workspaces', 'daily_emails_sent_count')) {
                $table->unsignedInteger('daily_emails_sent_count')->default(0)->after('daily_email_send_cap');
            }
            if (!Schema::hasColumn('workspaces', 'last_email_sent_date')) {
                $table->date('last_email_sent_date')->nullable()->after('daily_emails_sent_count');
            }
        });

        // 5. Expand Users with Super Admin 2FA Columns (Item 9 & 11)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
        });

        // 6. Seed Presets for Carwash, Construction Company, Auto Spare Parts (Item 3)
        $newPresets = [
            [
                'key' => 'carwash',
                'name' => 'Car Wash & Auto Detailing',
                'icon' => 'sparkles',
                'description' => 'Predefined services for auto washes, mobile detailing, ceramic coating, and valeting.',
                'items' => [
                    ['name' => 'Express Exterior Foam Wash & Hand Dry', 'category' => 'Wash', 'description' => 'High-pressure foam cannon wash, wheel face cleaning, and microfiber dry', 'unit' => 'vehicle', 'rate_cents' => 2500],
                    ['name' => 'Deluxe Interior Deep Clean & Vacuum', 'category' => 'Detailing', 'description' => 'Fabric shampoo, leather conditioner, dashboard UV dressing, and streak-free windows', 'unit' => 'vehicle', 'rate_cents' => 6500],
                    ['name' => 'Full Ceramic Paint Protection (1-Year)', 'category' => 'Protection', 'description' => 'Clay bar decontamination, 1-stage machine polish, and 9H ceramic nano-coating', 'unit' => 'package', 'rate_cents' => 35000],
                    ['name' => 'Engine Bay Steam Degrease & Dressing', 'category' => 'Specialty', 'description' => 'Safe electrical masking, steam degreasing, and matte finish dressing', 'unit' => 'service', 'rate_cents' => 4500],
                    ['name' => 'Headlight UV Restoration & Polish', 'category' => 'Restoration', 'description' => 'Multi-grit wet sand, high-gloss compound buff, and anti-UV ceramic seal', 'unit' => 'pair', 'rate_cents' => 5000],
                ],
            ],
            [
                'key' => 'construction_company',
                'name' => 'Construction & Contracting Services',
                'icon' => 'building',
                'description' => 'Standard line items for general contractors, civil engineers, and framing trades.',
                'items' => [
                    ['name' => 'Architectural Drafting & CAD Planning', 'category' => 'Planning', 'description' => 'Detailed blueprints, structural layout drawings, and permit submission pack', 'unit' => 'plan', 'rate_cents' => 180000],
                    ['name' => 'Concrete Foundation & Footing Pour', 'category' => 'Structural', 'description' => 'Grading, rebar cage installation, formwork, and 4000 PSI concrete pour', 'unit' => 'cubic_yard', 'rate_cents' => 450000],
                    ['name' => 'Structural Steel & Timber Framing', 'category' => 'Framing', 'description' => 'Load-bearing stud framing, roof trusses, subfloor sheeting, and hurricane anchors', 'unit' => 'sq_ft', 'rate_cents' => 320000],
                    ['name' => 'Drywall Hanging, Taping & Skim Coating', 'category' => 'Finishing', 'description' => 'Level 4 drywall installation, joint compound taping, and smooth skim coat', 'unit' => 'sq_ft', 'rate_cents' => 120000],
                    ['name' => 'Site Project Management & Supervision', 'category' => 'Supervision', 'description' => 'OSHA compliance oversight, subcontractor dispatch, and daily progress logs', 'unit' => 'hour', 'rate_cents' => 8500],
                ],
            ],
            [
                'key' => 'auto_spare_parts',
                'name' => 'Auto Spare Parts & Accessories',
                'icon' => 'cog',
                'description' => 'Standard retail replacement parts, lubricants, filters, and automotive batteries.',
                'items' => [
                    ['name' => 'Premium Ceramic Brake Pads (Front Set)', 'category' => 'Brakes', 'description' => 'OEM-fit low-dust ceramic pads with noise reduction shims', 'unit' => 'set', 'rate_cents' => 8500],
                    ['name' => 'Full Synthetic Motor Oil 5W-30 (5L Jug)', 'category' => 'Lubricants', 'description' => 'API SP / ILSAC GF-6A certified high-performance synthetic lubricant', 'unit' => 'jug', 'rate_cents' => 4500],
                    ['name' => 'Heavy Duty Spin-On Oil Filter', 'category' => 'Filters', 'description' => 'Micro-glass filter media with silicone anti-drainback valve (99% filtration)', 'unit' => 'unit', 'rate_cents' => 1500],
                    ['name' => 'AGM High-Performance Battery (12V 70Ah)', 'category' => 'Electrical', 'description' => 'Absorbent Glass Mat sealed battery with 760 Cold Cranking Amps (CCA)', 'unit' => 'unit', 'rate_cents' => 18500],
                    ['name' => 'Bi-Xenon / LED Headlamp Replacement Assembly', 'category' => 'Lighting', 'description' => 'DOT/SAE compliant direct plug-and-play projector lamp assembly', 'unit' => 'unit', 'rate_cents' => 22000],
                ],
            ],
        ];

        foreach ($newPresets as $p) {
            $preset = IndustryPreset::updateOrCreate(
                ['industry_key' => $p['key']],
                [
                    'industry_name' => $p['name'],
                    'icon' => $p['icon'],
                    'description' => $p['description'],
                ]
            );

            foreach ($p['items'] as $item) {
                PresetItem::updateOrCreate(
                    [
                        'industry_preset_id' => $preset->id,
                        'name' => $item['name'],
                    ],
                    [
                        'category' => $item['category'],
                        'description' => $item['description'],
                        'default_unit' => $item['unit'],
                        'suggested_rate_cents' => $item['rate_cents'],
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['portal_token_expires_at', 'failed_pin_attempts', 'pin_locked_until']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('unique_workspace_payment_number');
        });

        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['daily_email_send_cap', 'daily_emails_sent_count', 'last_email_sent_date']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
