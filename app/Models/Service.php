<?php

namespace App\Models;

use App\Models\Traits\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'name',
        'category',
        'description',
        'unit',
        'rate_cents',
        'default_tax_rate',
        'sku',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rate_cents' => 'integer',
        'default_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate_cents / 100, 2);
    }
}
