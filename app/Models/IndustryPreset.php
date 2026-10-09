<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndustryPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'industry_key',
        'industry_name',
        'icon',
        'description',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PresetItem::class);
    }
}
