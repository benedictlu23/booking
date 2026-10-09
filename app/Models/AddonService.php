<?php

namespace App\Models;

use Database\Factories\AddonServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'icon', 'pricing_type', 'price', 'is_active', 'sort_order'])]
class AddonService extends Model
{
    /** @use HasFactory<AddonServiceFactory> */
    use HasFactory;

    protected $table = 'room_addon_services';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function bookingAddons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }
}
