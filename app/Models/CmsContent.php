<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $fillable = [
        'key',
        'section',
        'content',
        'locale',
    ];

    /**
     * Helper to get CMS content with a default fallback
     */
    public static function getVal(string $key, string $default = ''): string
    {
        $item = static::where('key', $key)->first();
        return ($item && !empty($item->content)) ? $item->content : $default;
    }
}
