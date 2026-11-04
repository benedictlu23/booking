<?php

use App\Models\AddonService;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\HousekeepingLog;
use App\Models\ResortSetting;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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

test('adding an extra charge to an already paid booking changes payment status to partial with correct balance due', function () {
    $this->actingAs($this->admin);

    $booking = Booking::create([
        'booking_code' => 'PAID-ADDON-TEST',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'John Paid',
        'check_in' => now()->format('Y-m-d'),
        'check_out' => now()->addDay()->format('Y-m-d'),
        'booking_type' => 'overnight',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'amount_paid' => 2500,
        'status' => 'checked_in',
        'payment_status' => 'paid',
        'payment_method' => 'cash',
    ]);

    expect($booking->balance_due)->toBe(0.0)
        ->and($booking->isFullyPaid())->toBeTrue();

    // Guest orders mini-bar / extra items
    $chargeResponse = $this->post(route('admin.bookings.add-extra', $booking), [
        'addon_service_id' => null,
        'item_name' => 'Mini-bar & Snack Box',
        'unit_price' => 450,
        'quantity' => 1,
    ]);

    $chargeResponse->assertRedirect();
    $booking->refresh();

    // Total price is now 2950, amount_paid is still 2500, balance_due is 450, payment_status is partial!
    expect((float) $booking->total_price)->toBe(2950.0)
        ->and((float) $booking->amount_paid)->toBe(2500.0)
        ->and((float) $booking->balance_due)->toBe(450.0)
        ->and($booking->payment_status)->toBe('partial')
        ->and($booking->isFullyPaid())->toBeFalse();

    // Admin settles remaining balance
    $settleResponse = $this->post(route('admin.bookings.settle-payment', $booking), [
        'payment_method' => 'gcash',
        'amount' => 450,
    ]);

    $settleResponse->assertRedirect();
    $booking->refresh();

    expect((float) $booking->amount_paid)->toBe(2950.0)
        ->and((float) $booking->balance_due)->toBe(0.0)
        ->and($booking->payment_status)->toBe('paid');
});

test('admin walk-in booking correctly computes multi-night stay, pet fees, and immediate check-in', function () {
    $this->actingAs($this->admin);

    $this->room->update(['allows_pets' => true, 'pet_fee' => 200]);

    $checkIn = now()->format('Y-m-d\TH:i');
    $checkOut = now()->addDays(3)->format('Y-m-d\TH:i'); // 3 nights

    $response = $this->post(route('admin.bookings.store'), [
        'room_id' => $this->room->id,
        'guest_name' => 'Walkin Guest',
        'guest_phone' => '09998887777',
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'adults' => 2,
        'children' => 1,
        'pets' => 2,
        'booking_type' => 'overnight',
        'is_walk_in' => 1,
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'check_in_now' => 1,
    ]);

    $booking = Booking::where('guest_name', 'Walkin Guest')->first();

    expect($booking)->not->toBeNull()
        ->and($booking->is_walk_in)->toBeTrue()
        ->and($booking->status)->toBe('checked_in')
        ->and($booking->payment_status)->toBe('paid')
        ->and((float) $booking->base_price)->toBe(7500.0) // 2500 * 3 nights
        ->and((float) $booking->pet_fee_total)->toBe(1200.0) // 200 * 3 nights * 2 pets
        ->and((float) $booking->total_price)->toBe(8700.0)
        ->and((float) $booking->amount_paid)->toBe(8700.0)
        ->and($this->room->fresh()->status)->toBe('occupied');

    $response->assertRedirect(route('admin.bookings.show', $booking));
});

test('room with checked_out booking is available for new booking', function () {
    // Existing completed booking for today
    Booking::create([
        'booking_code' => 'CHKOUT-001',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'Departed Guest',
        'check_in' => now()->startOfDay(),
        'check_out' => now()->addDay()->startOfDay(),
        'booking_type' => 'overnight',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'status' => 'checked_out',
        'payment_status' => 'paid',
    ]);

    expect($this->room->isAvailableFor(now(), now()->addDay()))->toBeTrue();

    // Availability endpoint returns available = true
    $response = $this->getJson(route('rooms.availability', [
        'room' => $this->room->id,
        'check_in' => now()->format('Y-m-d'),
        'check_out' => now()->addDay()->format('Y-m-d'),
    ]));

    $response->assertOk()
        ->assertJson([
            'available' => true,
        ]);
});

test('room availability respects blackout dates configured in resort settings', function () {
    $blackoutDate = now()->addDays(15)->format('Y-m-d');
    ResortSetting::set('blackout_dates', $blackoutDate);

    // Dates overlapping blackout date should return false
    expect($this->room->isAvailableFor(now()->addDays(14), now()->addDays(16)))->toBeFalse();

    // Dates not overlapping blackout date should return true
    expect($this->room->isAvailableFor(now()->addDays(20), now()->addDays(22)))->toBeTrue();
});

test('view all rooms displays all published rooms including day_pass by default', function () {
    $dayPassRoom = Room::create([
        'category_id' => $this->category->id,
        'room_number' => 'DP-01',
        'name' => 'Day Cottage',
        'capacity_adults' => 4,
        'capacity_children' => 2,
        'base_price' => 1200,
        'status' => 'available',
        'booking_type' => 'day_pass',
        'is_active' => true,
    ]);

    $response = $this->get(route('rooms.search'));

    $response->assertOk()
        ->assertSee('Deluxe Garden')
        ->assertSee('Day Cottage');
});

test('room photo upload strictly synchronizes photo count without ghost entries', function () {
    $this->actingAs($this->admin);

    Storage::fake('public');

    $file1 = UploadedFile::fake()->image('photo1.jpg');
    $file2 = UploadedFile::fake()->image('photo2.jpg');
    $file3 = UploadedFile::fake()->image('photo3.jpg');

    $response = $this->put(route('admin.rooms.update', $this->room), [
        'category_id' => $this->category->id,
        'room_number' => '101',
        'name' => 'Deluxe Garden Updated',
        'capacity_adults' => 2,
        'capacity_children' => 1,
        'base_price' => 2500,
        'status' => 'available',
        'booking_type' => 'overnight',
        'is_active' => 1,
        'photos' => [$file1, $file2, $file3],
    ]);

    $response->assertRedirect();
    $this->room->refresh();

    // Verify exactly 3 photos created, no duplicates, gallery array length strictly 3
    expect($this->room->photos)->toHaveCount(3)
        ->and($this->room->gallery)->toHaveCount(3)
        ->and($this->room->photos->pluck('path')->toArray())->toEqual($this->room->gallery);
});

test('customer booking enforces same-day checkout for day pass and rejects overnight same-day checkout', function () {
    $this->actingAs($this->customer);

    $date = now()->addDays(5)->format('Y-m-d');

    // Day pass with same-day checkout succeeds and forces check_out to equal check_in
    $response = $this->post(route('booking.store'), [
        'room_id' => $this->room->id,
        'check_in' => $date,
        'check_out' => $date,
        'adults' => 2,
        'booking_type' => 'day_pass',
        'payment_method' => 'cash',
    ]);

    $response->assertSessionHasNoErrors();
    $booking = Booking::where('room_id', $this->room->id)->where('booking_type', 'day_pass')->first();
    expect($booking)->not->toBeNull()
        ->and(Carbon::parse($booking->check_in)->toDateString())->toBe($date)
        ->and(Carbon::parse($booking->check_out)->toDateString())->toBe($date);

    // Overnight booking with same-day checkout must fail validation
    $invalidOvernight = $this->from(route('booking.show', $this->room))->post(route('booking.store'), [
        'room_id' => $this->room->id,
        'check_in' => $date,
        'check_out' => $date,
        'adults' => 2,
        'booking_type' => 'overnight',
        'payment_method' => 'cash',
    ]);

    $invalidOvernight->assertSessionHasErrors(['check_out']);
});

test('admin booking enforces same-day checkout for day pass and rejects overnight same-day checkout', function () {
    $this->actingAs($this->admin);

    $date = now()->addDays(7)->format('Y-m-d');

    // Admin day pass succeeds
    $response = $this->post(route('admin.bookings.store'), [
        'room_id' => $this->room->id,
        'guest_name' => 'Admin Day Guest',
        'guest_phone' => '09191234567',
        'check_in' => $date.'T08:00',
        'check_out' => $date.'T17:00',
        'adults' => 1,
        'booking_type' => 'day_pass',
        'is_walk_in' => 1,
    ]);

    $response->assertSessionHasNoErrors();
    $booking = Booking::where('guest_name', 'Admin Day Guest')->first();
    expect($booking)->not->toBeNull()
        ->and($booking->booking_type)->toBe('day_pass')
        ->and(Carbon::parse($booking->check_in)->toDateString())->toBe($date)
        ->and(Carbon::parse($booking->check_out)->toDateString())->toBe($date);

    // Admin overnight with same datetime or same-day checkout where check_out is not after check_in must fail validation
    $invalidOvernight = $this->from(route('admin.bookings.create'))->post(route('admin.bookings.store'), [
        'room_id' => $this->room->id,
        'guest_name' => 'Admin Invalid Overnight',
        'check_in' => $date.'T12:00',
        'check_out' => $date.'T12:00',
        'adults' => 1,
        'booking_type' => 'overnight',
    ]);

    $invalidOvernight->assertSessionHasErrors(['check_out']);
});

test('cannot double book same room on same day for day pass', function () {
    $date = now()->addDays(12)->format('Y-m-d');

    // Existing Day Pass on $date
    Booking::create([
        'booking_code' => 'DAY-EXIST01',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'First Day Guest',
        'check_in' => $date.' 08:00:00',
        'check_out' => $date.' 17:00:00',
        'booking_type' => 'day_pass',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    // Room must now be unavailable for the same day
    expect($this->room->isAvailableFor(Carbon::parse($date), Carbon::parse($date)))->toBeFalse();

    // Availability API endpoint returns available = false
    $response = $this->getJson(route('rooms.availability', [
        'room' => $this->room->id,
        'check_in' => $date,
        'check_out' => $date,
        'booking_type' => 'day_pass',
    ]));

    $response->assertOk()
        ->assertJson([
            'available' => false,
            'message' => 'Room is already booked for these dates.',
        ]);

    // Customer attempting to book same day must be rejected
    $this->actingAs($this->customer);
    $storeResponse = $this->from(route('booking.show', $this->room))->post(route('booking.store'), [
        'room_id' => $this->room->id,
        'check_in' => $date,
        'check_out' => $date,
        'adults' => 2,
        'booking_type' => 'day_pass',
        'payment_method' => 'cash',
    ]);

    $storeResponse->assertSessionHasErrors(['room_id']);

    // Another day (e.g. $date + 1 day) must remain available
    $nextDate = now()->addDays(13)->format('Y-m-d');
    expect($this->room->isAvailableFor(Carbon::parse($nextDate), Carbon::parse($nextDate)))->toBeTrue();
});

test('cannot book overnight stay when room has day pass booking during the stay', function () {
    $date = now()->addDays(15)->format('Y-m-d');

    // Existing Day Pass on $date
    Booking::create([
        'booking_code' => 'DAY-EXIST02',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'Existing Day Guest',
        'check_in' => $date.' 08:00:00',
        'check_out' => $date.' 17:00:00',
        'booking_type' => 'day_pass',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    // Overnight booking starting on $date must be unavailable
    expect($this->room->isAvailableFor(Carbon::parse($date), Carbon::parse($date)->addDay()))->toBeFalse();
});

test('consecutive overnight bookings are allowed on checkout day', function () {
    $day1 = now()->addDays(20)->format('Y-m-d');
    $day2 = now()->addDays(21)->format('Y-m-d');
    $day3 = now()->addDays(22)->format('Y-m-d');

    // Guest 1 stays $day1 to $day2
    Booking::create([
        'booking_code' => 'STAY-GUEST01',
        'room_id' => $this->room->id,
        'user_id' => $this->customer->id,
        'guest_name' => 'Guest One',
        'check_in' => $day1.' 14:00:00',
        'check_out' => $day2.' 12:00:00',
        'booking_type' => 'overnight',
        'adults' => 2,
        'base_price' => 2500,
        'total_price' => 2500,
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]);

    // Guest 2 checking in on $day2 (departure day of Guest 1) and checking out on $day3 is allowed
    expect($this->room->isAvailableFor(Carbon::parse($day2), Carbon::parse($day3)))->toBeTrue();

    // Guest trying to book overlapping stay ($day1 to $day3) is NOT allowed
    expect($this->room->isAvailableFor(Carbon::parse($day1), Carbon::parse($day3)))->toBeFalse();
});
