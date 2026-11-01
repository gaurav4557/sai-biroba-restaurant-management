<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $table = 'credit_note_items';

    protected $fillable = [
        'credit_note_id',
        'bill_item_id',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_amount',
        'line_total',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class, 'credit_note_id');
    }

    public function billItem(): BelongsTo
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }
}
