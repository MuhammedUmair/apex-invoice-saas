<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'title',
        'placement',
        'type',
        'image_path',
        'destination_url',
        'alt_text',
        'open_in_new_tab',
        'html_code',
        'headline',
        'body_text',
        'badge_text',
        'cta_text',
        'theme_color',
        'is_active',
        'sort_order',
        'impressions_count',
        'clicks_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the primary active ad for a given placement
     */
    public static function getActive(?string $placement = null): ?self
    {
        $query = static::where('is_active', true);

        if ($placement) {
            $query->where(function ($q) use ($placement) {
                $q->where('placement', $placement)
                  ->orWhere('placement', 'all');
            });
        }

        return $query->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'desc')
                    ->first();
    }

    /**
     * Scope for active ads
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific placement
     */
    public function scopeForPlacement($query, string $placement)
    {
        return $query->where(function ($q) use ($placement) {
            $q->where('placement', $placement)
              ->orWhere('placement', 'all');
        });
    }

    /**
     * Helper to get styling gradient / theme classes
     */
    public function getThemeStyles(): array
    {
        return match ($this->theme_color) {
            'slate' => [
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 border-slate-700/80',
                'badge' => 'bg-slate-700/60 text-slate-300 border-slate-600/50',
                'btn' => 'bg-slate-700 hover:bg-slate-600 text-white',
                'glow' => 'bg-slate-500/20',
            ],
            'purple' => [
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-purple-950 to-indigo-950 border-purple-500/30',
                'badge' => 'bg-purple-500/20 text-purple-300 border-purple-400/30',
                'btn' => 'bg-purple-600 hover:bg-purple-700 text-white',
                'glow' => 'bg-purple-600/20',
            ],
            'emerald' => [
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-950 border-emerald-500/30',
                'badge' => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30',
                'btn' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
                'glow' => 'bg-emerald-600/20',
            ],
            'blue' => [
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-blue-950 to-slate-950 border-blue-500/30',
                'badge' => 'bg-blue-500/20 text-blue-300 border-blue-400/30',
                'btn' => 'bg-blue-600 hover:bg-blue-700 text-white',
                'glow' => 'bg-blue-600/20',
            ],
            'amber' => [
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-amber-950 to-orange-950 border-amber-500/30',
                'badge' => 'bg-amber-500/20 text-amber-300 border-amber-400/30',
                'btn' => 'bg-amber-600 hover:bg-amber-700 text-white',
                'glow' => 'bg-amber-600/20',
            ],
            default => [ // 'brand' (red maple)
                'card_bg' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-red-950 border-red-500/30',
                'badge' => 'bg-red-500/20 text-red-300 border-red-400/30',
                'btn' => 'bg-red-600 hover:bg-red-700 text-white',
                'glow' => 'bg-red-600/20',
            ],
        };
    }
}
