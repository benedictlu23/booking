<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id', 'room_number', 'name', 'description', 'gallery',
    'capacity_adults', 'capacity_children', 'allows_pets', 'pet_fee',
    'base_price', 'floor_number', 'status', 'booking_type', 'is_active',
])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'rooms';

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'allows_pets' => 'boolean',
            'is_active' => 'boolean',
            'base_price' => 'decimal:2',
            'pet_fee' => 'decimal:2',
            'capacity_adults' => 'integer',
            'capacity_children' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_room_amenities', 'room_id', 'amenity_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RoomPhoto::class)->orderBy('sort_order');
    }

    public function coverPhoto(): ?RoomPhoto
    {
        return $this->photos()->where('is_cover', true)->first()
            ?? $this->photos()->first();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function housekeepingLogs(): HasMany
    {
        return $this->hasMany(HousekeepingLog::class)->latest();
    }

    public function isAvailableFor(\DateTimeInterface $checkIn, \DateTimeInterface $checkOut): bool
    {
        if (! $this->is_active || in_array($this->status, ['maintenance', 'out_of_service', 'blocked'])) {
            return false;
        }

        return ! $this->bookings()
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
            ->exists();
    }
}
