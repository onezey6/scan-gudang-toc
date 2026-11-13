<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'reference_no',
        'item_id',
        'user_id',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'source_destination',
        'partner_name',
        'scan_method',
        'handover_document_id',
        'notes',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handoverDocument(): BelongsTo
    {
        return $this->belongsTo(HandoverDocument::class);
    }
}
