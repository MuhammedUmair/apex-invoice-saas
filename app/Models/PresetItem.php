<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'industry_preset_id',
        'name',
        'category',
        'description',
        'default_unit',
        'suggested_rate_cents',
    ];

    protected $casts = [
        'suggested_rate_cents' => 'integer',
    ];

    public function preset(): BelongsTo
    {
        return $this->belongsTo(IndustryPreset::class, 'industry_preset_id');
    }
}
