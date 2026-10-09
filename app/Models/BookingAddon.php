<?php

namespace App\Models;

use Database\Factories\BookingAddonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'addon_service_id', 'item_name', 'quantity', 'unit_price', 'subtotal'])]
class BookingAddon extends Model
{
    /** @use HasFactory<BookingAddonFactory> */
    use HasFactory;

    protected $table = 'room_booking_addons';

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function addonService(): BelongsTo
    {
        return $this->belongsTo(AddonService::class);
    }
}
