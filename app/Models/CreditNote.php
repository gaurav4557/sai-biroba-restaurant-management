<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    use HasFactory;

    protected $table = 'credit_notes';

    protected $fillable = [
        'credit_note_number',
        'bill_id',
        'table_visit_id',
        'authorized_by',
        'issued_by',
        'subtotal_credited',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'total_tax_credited',
        'total_credit_amount',
        'reason',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_credited' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'total_tax_credited' => 'decimal:2',
            'total_credit_amount' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function tableVisit(): BelongsTo
    {
        return $this->belongsTo(TableVisit::class, 'table_visit_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id');
    }
}
