<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    public const STATUS_SEDIA = 'sedia';
    public const STATUS_SEDANG_KELUAR = 'sedang_keluar';
    public const STATUS_SEDANG_DIPAKAI = 'sedang_dipakai';
    public const STATUS_SEDANG_DIPINJAM = 'sedang_dipinjam';

    protected $fillable = [
        'item_code',
        'barcode',
        'name',
        'photo_path',
        'category_id',
        'item_type',
        'brand',
        'unit',
        'stock',
        'min_stock',
        'storage_location',
        'condition_status',
        'availability_status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $appends = [
        'photo_url',
        'availability_label',
    ];

    public static function availabilityOptions(): array
    {
        return [
            self::STATUS_SEDIA => 'Sedia',
            self::STATUS_SEDANG_KELUAR => 'Sedang Keluar',
            self::STATUS_SEDANG_DIPAKAI => 'Sedang Dipakai',
            self::STATUS_SEDANG_DIPINJAM => 'Sedang Dipinjam',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!empty($this->photo_path)) {
            return asset($this->photo_path);
        }

        return null;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return self::availabilityOptions()[$this->availability_status ?? self::STATUS_SEDIA] ?? 'Sedia';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function handoverItems(): HasMany
    {
        return $this->hasMany(HandoverItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
