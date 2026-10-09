<?php

use App\Models\AddonService;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\HousekeepingLog;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;

beforeEach(function () {
    $this->category = RoomCategory::create([
        'name' => 'Standard Deluxe',
        'slug' => 'standard-deluxe',
        'description' => 'Comfortable deluxe room',
        'is_active' => true,
    ]);

    $this->room = Room::create([
        'category_id' => $this->category->id,
        'room_number' => '101',
        'name' => 'Deluxe Garden',
        'capacity_adults' => 2,
        'capacity_children' => 1,
        'base_price' => 2500,
        'status' => 'available',
        'booking_type' => 'overnight',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->customer = User::factory()->create([
        'role' => 'customer',
    ]);
});

test('room availability endpoint returns correct availability status', function () {
    $response = $this->getJson(route('rooms.availability', [
        'room' => $this->room->id,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(7)->format('Y-m-d'),
    ]));

    $response->assertOk()
        ->assertJson([
            'available' => true,
            'nights' => 2,
            'base_total' => 5000,
        ]);
});

test('public booking creates reservation without requiring add-ons', function () {
    $this->actingAs($this->customer);

    $checkIn = now()->addDays(10)->format('Y-m-d');
    $checkOut = now()->addDays(12)->format('Y-m-d');

    $response = $this->post(route('booking.store'), [
        'room_id' => $this->room->id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adults' => 2,
        'children' => 0,
        'booking_type' => 'overnight',
        'payment_method' => 'gcash',
    ]);

    $booking = Booking::first();

    expect($booking)->not->toBeNull()
        ->and($booking->room_id)->toBe($this->room->id)
        ->and((float) $booking->base_price)->toBe(5000.0)
        ->and((float) $booking->addons_total)->toBe(0.0)
        ->and((float) $booking->total_price)->toBe(5000.0)
        ->and($booking->payment_status)->toBe('unpaid');

    $response->assertRedirect(route('booking.confirmation', $booking));
});

test('discount calculation handles minimum spend and float casting properly', function () {
    $discount = Discount::create([
        'code' => 'PROMO500',
        'name' => 'Flat Discount 500',
        'type' => 'fixed',
        'value' => 500,
        'minimum_amount' => 3000,
        'is_active' => true,
    ]);

    $responseBelow = $this->getJson(route('discounts.validate', [
        'code' => 'PROMO500',
        'amount' => 2000,
    ]));

    $responseBelow->assertOk()
        ->assertJson(['valid' => false]);

    $responseValid = $this->getJson(route('discounts.validate', [
        'code' => 'PROMO500',
        'amount' => 4000,
    ]));

    $responseValid->assertOk()
        ->assertJson([
            'valid' => true,
            'code' => 'PROMO500',
            'discount_amount' => 500,
        ]);
});

test('admin can quick toggle room maintenance status', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('admin.rooms.quick-status', $this->room), [
        'status' => 'maintenance',
    ]);

    $response->assertRedirect();
    expect($this->room->fresh()->status)->toBe('maintenance');

    $response2 = $this->post(route('admin.rooms.quick-status', $this->room), [
        'status' => 'out_of_service',
    ]);

    $response2->assertRedirect();
    expect($this->room->fresh()->status)->toBe('out_of_service');
});

test('admin can settle booking payment and attach extra charges', function () {
    $this->actingAs($this->admin);

    $booking = Booking::create([
        'booking_code' => 'AGRO-TEST01',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'John Doe',
        'check_in' => now()->format('Y-m-d'),
        'check_out' => now()->addDay()->format('Y-m-d'),
        'booking_type' => 'overnight',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'status' => 'confirmed',
        'payment_status' => 'unpaid',
    ]);

    $addon = AddonService::create([
        'name' => 'Extra Bed',
        'price' => 350,
        'pricing_type' => 'fixed',
        'is_active' => true,
    ]);

    // Attach predefined extra charge
    $chargeResponse = $this->post(route('admin.bookings.add-extra', $booking), [
        'addon_service_id' => $addon->id,
        'quantity' => 2,
    ]);

    $chargeResponse->assertRedirect();
    $booking->refresh();

    expect((float) $booking->addons_total)->toBe(700.0)
        ->and((float) $booking->total_price)->toBe(3200.0);

    // Attach custom item charge (no addon_service_id, e.g. "bed sheet")
    $customChargeResponse = $this->post(route('admin.bookings.add-extra', $booking), [
        'addon_service_id' => null,
        'item_name' => 'bed sheet',
        'unit_price' => 200,
        'quantity' => 2,
    ]);

    $customChargeResponse->assertRedirect();
    $booking->refresh();

    expect((float) $booking->addons_total)->toBe(1100.0)
        ->and((float) $booking->total_price)->toBe(3600.0);

    $customItem = $booking->addons()->where('item_name', 'bed sheet')->first();
    expect($customItem)->not->toBeNull()
        ->and($customItem->addon_service_id)->toBeNull()
        ->and((float) $customItem->subtotal)->toBe(400.0);

    // Settle payment
    $settleResponse = $this->post(route('admin.bookings.settle-payment', $booking), [
        'payment_method' => 'gcash',
    ]);

    $settleResponse->assertRedirect();
    $booking->refresh();

    expect($booking->payment_status)->toBe('paid')
        ->and($booking->payment_method)->toBe('gcash');
});

test('housekeeping logs can be recorded and audited', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('admin.housekeeping.store'), [
        'room_id' => $this->room->id,
        'staff_name' => 'Maria Cleaning',
        'status' => 'clean',
        'notes' => 'Room fully sanitized and prepared',
    ]);

    $response->assertRedirect();

    $log = HousekeepingLog::first();
    expect($log)->not->toBeNull()
        ->and($log->staff_name)->toBe('Maria Cleaning')
        ->and($log->status)->toBe('clean')
        ->and($log->room_id)->toBe($this->room->id);
});

test('customer history is accessible to admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.customers.index'));
    $response->assertOk()
        ->assertSee($this->customer->name);

    $showResponse = $this->get(route('admin.customers.show', $this->customer));
    $showResponse->assertOk()
        ->assertSee($this->customer->name);
});

test('admin can checkout booking, assign housekeeper, and housekeeper confirms clean to make room available', function () {
    $housekeeper = User::factory()->create([
        'name' => 'Kris Housekeeper',
        'role' => 'housekeeper',
    ]);

    $this->room->update(['status' => 'occupied']);

    $booking = Booking::create([
        'booking_code' => 'TEST-CHK01',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'Checkout Guest',
        'check_in' => now()->subDay(),
        'check_out' => now(),
        'booking_type' => 'overnight',
        'adults' => 2,
        'children' => 0,
        'pets' => 0,
        'base_price' => 2500,
        'addons_total' => 0,
        'pet_fee_total' => 0,
        'discount_amount' => 0,
        'total_price' => 2500,
        'status' => 'checked_in',
        'payment_status' => 'paid',
    ]);

    // Admin checks out and assigns housekeeper
    $this->actingAs($this->admin);
    $checkoutResponse = $this->post(route('admin.bookings.check-out', $booking), [
        'housekeeper_id' => $housekeeper->id,
        'housekeeping_notes' => 'Please sanitize and change beddings.',
    ]);

    $checkoutResponse->assertRedirect();
    $booking->refresh();
    $this->room->refresh();

    expect($booking->status)->toBe('checked_out')
        ->and($this->room->status)->toBe('housekeeping');

    $log = HousekeepingLog::where('room_id', $this->room->id)->latest()->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($housekeeper->id)
        ->and($log->staff_name)->toBe($housekeeper->name)
        ->and($log->status)->toBe('dirty');

    // Housekeeper logs in, sees task, starts cleaning, and confirms clean
    $this->actingAs($housekeeper);
    $hkDashboard = $this->get(route('housekeeper.dashboard'));
    $hkDashboard->assertOk()
        ->assertSee('Room 101')
        ->assertSee('Please sanitize and change beddings.');

    // Housekeeper starts cleaning
    $startResponse = $this->post(route('housekeeper.logs.start', $log));
    $startResponse->assertRedirect();
    $log->refresh();
    expect($log->status)->toBe('in_progress');

    // Housekeeper confirms clean
    $confirmResponse = $this->post(route('housekeeper.logs.confirm', $log), [
        'notes' => 'Room thoroughly cleaned and prepared for new guest.',
    ]);
    $confirmResponse->assertRedirect();

    $log->refresh();
    $this->room->refresh();

    expect($log->status)->toBe('clean')
        ->and($this->room->status)->toBe('available');
});
