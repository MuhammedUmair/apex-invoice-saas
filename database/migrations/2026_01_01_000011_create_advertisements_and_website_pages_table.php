<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Advertisements Management Table
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('placement')->default('invoice_sidebar'); // invoice_sidebar, app_sidebar, landing_page, client_portal, all
            $table->enum('type', ['image', 'html', 'text'])->default('text'); // image upload, html embed, text-based
            $table->string('image_path')->nullable();
            $table->text('destination_url')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('open_in_new_tab')->default(true);
            $table->longText('html_code')->nullable();
            $table->string('headline')->nullable();
            $table->text('body_text')->nullable();
            $table->string('badge_text')->nullable()->default('SPONSORED PROMO');
            $table->string('cta_text')->nullable()->default('Learn More');
            $table->string('theme_color')->default('brand'); // brand, slate, purple, emerald, blue, amber
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();
        });

        // 2. Custom Website Pages Table (Website Builder)
        Schema::create('website_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('show_in_nav')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default advertisements so existing UI slots are immediately functional via DB
        DB::table('advertisements')->insert([
            [
                'title' => 'RedMaple Custom App Development',
                'placement' => 'invoice_sidebar',
                'type' => 'text',
                'image_path' => null,
                'destination_url' => 'https://redmapleagency.com',
                'alt_text' => 'RedMaple Studio Promo',
                'open_in_new_tab' => true,
                'html_code' => null,
                'headline' => 'Scale Your Business with Custom Web & Mobile Apps',
                'body_text' => 'Dedicated full-stack developers, custom CRM integrations, and turnkey SaaS development with RedMaple.',
                'badge_text' => 'SPONSORED PROMO',
                'cta_text' => 'Get Agency Quote',
                'theme_color' => 'brand',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Grow with RedMaple Studio (Sidebar Pro)',
                'placement' => 'app_sidebar',
                'type' => 'text',
                'image_path' => null,
                'destination_url' => 'https://redmapleagency.com',
                'alt_text' => 'RedMaple Pro Suite',
                'open_in_new_tab' => true,
                'html_code' => null,
                'headline' => 'Grow with RedMaple Studio',
                'body_text' => 'Custom Apps, CRM & Dedicated Developer Pods for agencies.',
                'badge_text' => 'PRO SUITE',
                'cta_text' => 'Upgrade & Save 20%',
                'theme_color' => 'slate',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed default website pages
        DB::table('website_pages')->insert([
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>About Invoicy by RedMaple</h2><p>Invoicy is an enterprise-grade global invoicing, automated payment reminders, and financial accounting SaaS platform designed for high-performing agencies, consultants, and commercial businesses.</p><p>Headquartered globally with support for international multi-currency billing and compliance across the US, UK, UAE, Pakistan, Europe, and Asia.</p>',
                'meta_description' => 'Learn about Invoicy by RedMaple — the modern financial accounting and invoicing suite for global enterprises.',
                'is_published' => true,
                'show_in_nav' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2><p>Welcome to Invoicy by RedMaple. By using our invoicing services, APIs, client portal, or recurring subscription management software, you agree to comply with our commercial terms and local taxation laws.</p><p>For inquiries or enterprise SLA agreements, contact support@redmapleagency.com.</p>',
                'meta_description' => 'Commercial terms of service for Invoicy SaaS platform.',
                'is_published' => true,
                'show_in_nav' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy & Data Protection Policy</h2><p>We take client billing privacy and financial data security seriously. Invoicy uses AES-256 encryption at rest and TLS 1.3 transit encryption. We do not sell user data to third parties.</p>',
                'meta_description' => 'Privacy and financial data protection policy for Invoicy users and clients.',
                'is_published' => true,
                'show_in_nav' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('website_pages');
        Schema::dropIfExists('advertisements');
    }
};
