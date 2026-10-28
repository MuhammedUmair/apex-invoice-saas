<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsitePage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'is_published',
        'show_in_nav',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_nav' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for published pages
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for pages to show in main navigation
     */
    public function scopeInNav($query)
    {
        return $query->where('is_published', true)->where('show_in_nav', true)->orderBy('sort_order', 'asc');
    }
}
