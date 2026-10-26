<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocArticle extends Model
{
    protected $fillable = [
        'slug',
        'category',
        'title_en',
        'title_ur',
        'title_ar',
        'title_hi',
        'title_tr',
        'content_en',
        'content_ur',
        'content_ar',
        'content_hi',
        'content_tr',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get title in the current active locale, falling back to English
     */
    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $field = "title_{$locale}";
        return (!empty($this->{$field})) ? $this->{$field} : $this->title_en;
    }

    /**
     * Get content in the current active locale, falling back to English
     */
    public function getLocalizedContent(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $field = "content_{$locale}";
        return (!empty($this->{$field})) ? $this->{$field} : $this->content_en;
    }
}
