<?php

namespace Database\Seeders;

use App\Models\IndustryPreset;
use App\Models\PresetItem;
use Illuminate\Database\Seeder;

class IndustryPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            [
                'key' => 'web_development',
                'name' => 'Web Development & Hosting',
                'icon' => 'globe',
                'description' => 'Predefined services for web agencies, developers, and hosting providers.',
                'items' => [
                    ['name' => 'Custom Website Design & Development', 'category' => 'Web', 'description' => 'Full responsive web application build', 'unit' => 'project', 'rate_cents' => 150000],
                    ['name' => 'Managed Cloud Hosting (Monthly)', 'category' => 'Hosting', 'description' => 'High performance NVMe cloud hosting with SSL & daily backups', 'unit' => 'month', 'rate_cents' => 4900],
                    ['name' => 'WordPress Maintenance & Security', 'category' => 'Maintenance', 'description' => 'Core updates, malware scanning, and uptime monitoring', 'unit' => 'month', 'rate_cents' => 9900],
                    ['name' => 'Frontend Bug Fix / Hourly Consulting', 'category' => 'Consulting', 'description' => 'Senior developer technical support and optimization', 'unit' => 'hour', 'rate_cents' => 7500],
                    ['name' => 'Domain Name Registration (.com)', 'category' => 'Domains', 'description' => 'Annual domain registration and DNS management', 'unit' => 'year', 'rate_cents' => 2000],
                ],
            ],
            [
                'key' => 'digital_marketing',
                'name' => 'Digital Marketing & SMM',
                'icon' => 'megaphone',
                'description' => 'Services tailored for social media agencies, SEO consultants, and ad managers.',
                'items' => [
                    ['name' => 'Social Media Management (12 Posts/mo)', 'category' => 'SMM', 'description' => 'Content calendar creation, copywriting, and graphic design', 'unit' => 'month', 'rate_cents' => 60000],
                    ['name' => 'Meta & Google Ads Campaign Management', 'category' => 'Paid Ads', 'description' => 'Conversion tracking setup, A/B testing, and weekly optimization', 'unit' => 'month', 'rate_cents' => 50000],
                    ['name' => 'Search Engine Optimization (SEO) Retainer', 'category' => 'SEO', 'description' => 'Technical audit, keyword tracking, and backlink building', 'unit' => 'month', 'rate_cents' => 80000],
                    ['name' => 'Email Marketing Campaign (Newsletter)', 'category' => 'Email', 'description' => 'Email sequence design and broadcast scheduling', 'unit' => 'campaign', 'rate_cents' => 25000],
                ],
            ],
            [
                'key' => 'embroidery_apparel',
                'name' => 'Embroidery Digitizing & Vector Art',
                'icon' => 'tag',
                'description' => 'Preset services for garment digitizers, vector artwork designers, and print shops.',
                'items' => [
                    ['name' => 'Left Chest / Hat Logo Digitizing (EMB/DST)', 'category' => 'Digitizing', 'description' => 'Precision commercial embroidery punch with stitch run sheet', 'unit' => 'design', 'rate_cents' => 1500],
                    ['name' => 'Jacket Back Large Embroidery Digitizing', 'category' => 'Digitizing', 'description' => 'High stitch-count digitizing for outerwear and uniform backs', 'unit' => 'design', 'rate_cents' => 4500],
                    ['name' => 'Raster to Vector Art Conversion (AI/EPS)', 'category' => 'Vector', 'description' => 'Clean manual tracing into scalable vector graphics', 'unit' => 'artwork', 'rate_cents' => 1800],
                    ['name' => 'Color Separation for Screen Printing', 'category' => 'Screen Print', 'description' => 'Simulated process / spot color separations with reg marks', 'unit' => 'design', 'rate_cents' => 3000],
                ],
            ],
            [
                'key' => 'graphic_branding',
                'name' => 'Graphic Design & Brand Identity',
                'icon' => 'palette',
                'description' => 'Corporate branding, logo packages, stationery, and collateral design.',
                'items' => [
                    ['name' => 'Complete Brand Identity Guidelines', 'category' => 'Branding', 'description' => 'Primary logo, color palette, typography hierarchy, and brand book', 'unit' => 'package', 'rate_cents' => 120000],
                    ['name' => 'Corporate Stationery Set (Card + Letterhead)', 'category' => 'Print', 'description' => 'Print-ready business cards, letterhead, and envelope kit', 'unit' => 'set', 'rate_cents' => 30000],
                    ['name' => 'Brochure / Company Profile Design (8 Pages)', 'category' => 'Print', 'description' => 'Multi-page corporate sales deck / marketing booklet', 'unit' => 'booklet', 'rate_cents' => 50000],
                ],
            ],
            [
                'key' => 'legal_consulting',
                'name' => 'Consulting & Professional Services',
                'icon' => 'briefcase',
                'description' => 'General business advisory, accounting, and consulting contracts.',
                'items' => [
                    ['name' => 'Business Strategy Consultation', 'category' => 'Advisory', 'description' => '1-on-1 strategic session and roadmap delivery', 'unit' => 'hour', 'rate_cents' => 15000],
                    ['name' => 'Monthly Bookkeeping & Tax Prep Retainer', 'category' => 'Accounting', 'description' => 'Bank reconciliation and monthly financial statements', 'unit' => 'month', 'rate_cents' => 45000],
                ],
            ],
        ];

        foreach ($presets as $p) {
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
}
