<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverItem extends Model
{
    protected $fillable = [
        'handover_document_id',
        'item_id',
        'quantity',
        'serial_number',
        'accessories',
        'condition_status',
        'availability_status',
        'notes',
    ];

    public function getAvailabilityLabelAttribute(): string
    {
        return Item::availabilityOptions()[$this->availability_status ?? Item::STATUS_SEDIA] ?? 'Sedia';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(HandoverDocument::class, 'handover_document_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
