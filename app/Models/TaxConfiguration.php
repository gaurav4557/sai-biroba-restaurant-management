<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxConfiguration extends Model
{
    use HasFactory;

    protected $table = 'tax_configurations';

    protected $fillable = [
        'tax_name',
        'cgst_rate',
        'sgst_rate',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cgst_rate' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'total_rate' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'tax_configuration_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', now()->toDateString());
            });
    }
}
