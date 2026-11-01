<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';

    protected $fillable = [
        'table_visit_id',
        'bill_number',
        'tax_configuration_id',
        'generated_by',
        'status',
        'subtotal_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'discount_reason',
        'discount_approved_by',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'total_tax_amount',
        'service_charge',
        'round_off',
        'final_amount',
        'total_paid_amount',
        'total_refunded_amount',
        'issued_at',
        'settled_at',
        'voided_at',
        'voided_by',
        'void_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'total_tax_amount' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'round_off' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'total_paid_amount' => 'decimal:2',
            'total_refunded_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'settled_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function visit(): BelongsTo
    {
        return $this->belongsTo(TableVisit::class, 'table_visit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    public function successfulPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bill_id')->where('status', 'successful');
    }

    public function taxConfiguration(): BelongsTo
    {
        return $this->belongsTo(TaxConfiguration::class, 'tax_configuration_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function discountApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class, 'bill_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'bill_id');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'issued', 'partially_paid']);
    }

    public function scopeVoided(Builder $query): Builder
    {
        return $query->where('status', 'voided');
    }

    public function scopeBilledSales(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'paid']);
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return max(0.0, round((float) $this->final_amount - (float) $this->total_paid_amount, 2));
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return '₹' . number_format((float) $this->final_amount, 2);
    }

    public function getFormattedOutstandingBalanceAttribute(): string
    {
        return '₹' . number_format($this->outstanding_balance, 2);
    }
}
