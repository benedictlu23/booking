<?php

namespace Database\Seeders;

use App\Models\AddonService;
use App\Models\Amenity;
use App\Models\ResortSetting;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── Admin User ─────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@agro-inland.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'desk@agro-inland.test'],
            [
                'name' => 'Front Desk Staff',
                'password' => Hash::make('password'),
                'role' => 'desk_staff',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'guest@example.com'],
            [
                'name' => 'Sample Guest',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'visit_count' => 3,
                'email_verified_at' => now(),
            ]
        );

        // ─── Resort Settings ─────────────────────────────────────────
        $settings = [
            'resort_name' => 'Agro-Inland Resort',
            'resort_address' => 'Brgy. Sample, Municipality, Province, Philippines',
            'latitude' => '14.5995',
            'longitude' => '120.9842',
            'contact_details' => '+63 912 345 6789 | info@agro-inland.ph',
            'pet_policy' => 'Pets are welcome! A small fee applies per pet per night. Please keep pets on a leash in common areas.',
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
        ];

        foreach ($settings as $key => $value) {
            ResortSetting::set($key, $value, 'general');
        }

        // ─── Room Categories ─────────────────────────────────────────
        $categories = [
            ['name' => 'Deluxe Room', 'slug' => 'deluxe-room', 'description' => 'Comfortable rooms with garden view', 'is_active' => true],
            ['name' => 'Family Suite', 'slug' => 'family-suite', 'description' => 'Spacious suites perfect for families', 'is_active' => true],
            ['name' => 'Bahay Kubo Cottage', 'slug' => 'bahay-kubo-cottage', 'description' => 'Traditional Philippine bamboo cottages', 'is_active' => true],
            ['name' => 'Private Villa', 'slug' => 'private-villa', 'description' => 'Exclusive villas with private garden', 'is_active' => true],
        ];

        foreach ($categories as $cat) {
            RoomCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // ─── Amenities ───────────────────────────────────────────────
        $amenities = [
            'Air Conditioning', 'WiFi', 'Hot Shower', 'Smart TV', 'Mini-Fridge',
            'Private Bathroom', 'Outdoor Seating', 'BBQ Grill', 'Farm View', 'Balcony',
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity]);
        }

        // ─── Rooms ───────────────────────────────────────────────────
        $deluxe = RoomCategory::where('name', 'Deluxe Room')->first();
        $family = RoomCategory::where('name', 'Family Suite')->first();
        $cottage = RoomCategory::where('name', 'Bahay Kubo Cottage')->first();
        $villa = RoomCategory::where('name', 'Private Villa')->first();

        $rooms = [
            [
                'category_id' => $deluxe->id,
                'room_number' => 'DR-01',
                'name' => 'Garden Deluxe',
                'description' => 'A cozy deluxe room with a beautiful garden view and modern amenities.',
                'capacity_adults' => 2,
                'capacity_children' => 1,
                'allows_pets' => false,
                'pet_fee' => 0,
                'base_price' => 2500.00,
                'status' => 'available',
                'booking_type' => 'both',
                'is_active' => true,
            ],
            [
                'category_id' => $deluxe->id,
                'room_number' => 'DR-02',
                'name' => 'Farm View Deluxe',
                'description' => 'Enjoy waking up to the sights and sounds of the farm.',
                'capacity_adults' => 2,
                'capacity_children' => 2,
                'allows_pets' => true,
                'pet_fee' => 300.00,
                'base_price' => 2800.00,
                'status' => 'available',
                'booking_type' => 'both',
                'is_active' => true,
            ],
            [
                'category_id' => $family->id,
                'room_number' => 'FS-01',
                'name' => 'Harvest Family Suite',
                'description' => 'Perfect for families, with two bedrooms and a living area.',
                'capacity_adults' => 4,
                'capacity_children' => 3,
                'allows_pets' => true,
                'pet_fee' => 400.00,
                'base_price' => 5500.00,
                'status' => 'available',
                'booking_type' => 'overnight',
                'is_active' => true,
            ],
            [
                'category_id' => $cottage->id,
                'room_number' => 'BK-01',
                'name' => 'Lakeside Bahay Kubo',
                'description' => 'A traditional bamboo cottage right next to our stocked fishing pond.',
                'capacity_adults' => 4,
                'capacity_children' => 2,
                'allows_pets' => true,
                'pet_fee' => 350.00,
                'base_price' => 3800.00,
                'status' => 'available',
                'booking_type' => 'both',
                'is_active' => true,
            ],
            [
                'category_id' => $villa->id,
                'room_number' => 'PV-01',
                'name' => 'Sunset Private Villa',
                'description' => 'Our most exclusive accommodation with a private garden and BBQ area.',
                'capacity_adults' => 6,
                'capacity_children' => 4,
                'allows_pets' => true,
                'pet_fee' => 500.00,
                'base_price' => 9800.00,
                'status' => 'available',
                'booking_type' => 'overnight',
                'is_active' => true,
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::firstOrCreate(['room_number' => $roomData['room_number']], $roomData);
        }

        // ─── Addon Services ───────────────────────────────────────────
        $addons = [
            ['name' => 'Farm-Fresh Breakfast', 'description' => 'A hearty breakfast made with ingredients fresh from our farm.', 'icon' => '🍳', 'price' => 350.00, 'pricing_type' => 'per_person', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'BBQ Package', 'description' => 'Full BBQ setup with charcoal grill and marinated meats.', 'icon' => '🍖', 'price' => 800.00, 'pricing_type' => 'flat', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Fishing Rod Rental', 'description' => 'Fish in our stocked pond with full equipment.', 'icon' => '🎣', 'price' => 200.00, 'pricing_type' => 'flat', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Farm Tour', 'description' => 'Guided tour of our agro-farm with a local guide.', 'icon' => '🌿', 'price' => 150.00, 'pricing_type' => 'per_person', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Airport/City Transfer', 'description' => 'Round-trip van transfer to/from the resort.', 'icon' => '🚐', 'price' => 1200.00, 'pricing_type' => 'flat', 'sort_order' => 5, 'is_active' => true],
        ];

        foreach ($addons as $addon) {
            AddonService::firstOrCreate(['name' => $addon['name']], $addon);
        }
    }
}
