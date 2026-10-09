<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'google_id', 'avatar',
    'role', 'visit_count', 'last_visited_at',
    'phone_number', 'email_verified_at', 'facebook_id',
    'google_avatar_url', 'avatar_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_visited_at' => 'datetime',
            'password' => 'hashed',
            'visit_count' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDeskStaff(): bool
    {
        return $this->role === 'desk_staff';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isHousekeeper(): bool
    {
        return $this->role === 'housekeeper';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'desk_staff', 'housekeeper']);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function housekeepingLogs(): HasMany
    {
        return $this->hasMany(HousekeepingLog::class);
    }
}
