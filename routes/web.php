<?php

use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\HousekeepingController;
use App\Http\Controllers\Admin\ResortSettingController;
use App\Http\Controllers\Admin\RoomCategoryController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Housekeeper\HousekeeperController;
use App\Http\Controllers\RoomSearchController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', [RoomSearchController::class, 'index'])->name('home');
Route::get('/rooms', [RoomSearchController::class, 'search'])->name('rooms.search');
Route::get('/rooms/{room}', [RoomSearchController::class, 'show'])->name('rooms.show');
Route::get('/rooms/{room}/availability', [BookingController::class, 'checkAvailability'])->name('rooms.availability');
Route::get('/discounts/validate', [BookingController::class, 'validateDiscount'])->name('discounts.validate');

// ─── Authentication ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ─── Customer Portal ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-bookings', [CustomerDashboardController::class, 'bookings'])->name('customer.bookings');
    Route::get('/my-bookings/{booking}', [CustomerDashboardController::class, 'showBooking'])->name('customer.bookings.show');

    // Booking Flow
    Route::get('/book/{room}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/book', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/confirmation/{booking}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
    Route::post('/booking/{booking}/payment-proof', [BookingController::class, 'submitPaymentProof'])->name('booking.payment-proof');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
});

// ─── Housekeeper Portal ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:housekeeper,admin,desk_staff'])->prefix('housekeeper')->name('housekeeper.')->group(function () {
    Route::get('/', [HousekeeperController::class, 'index'])->name('dashboard');
    Route::post('/logs/{housekeepingLog}/start', [HousekeeperController::class, 'startCleaning'])->name('logs.start');
    Route::post('/logs/{housekeepingLog}/confirm', [HousekeeperController::class, 'confirm'])->name('logs.confirm');
    Route::post('/rooms/{room}/confirm-clean', [HousekeeperController::class, 'confirmRoomClean'])->name('rooms.confirm-clean');
});

// ─── Admin Panel ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin,desk_staff'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Reservation Matrix
    Route::get('/matrix', [AdminBookingController::class, 'matrix'])->name('bookings.matrix');

    // Bookings Management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/check-in', [AdminBookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/check-out', [AdminBookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/settle-payment', [AdminBookingController::class, 'settlePayment'])->name('bookings.settle-payment');
    Route::post('/bookings/{booking}/addons', [AdminBookingController::class, 'addExtraCharge'])->name('bookings.add-extra');

    // Quick Room Status
    Route::post('/rooms/{room}/quick-status', [AdminRoomController::class, 'quickStatus'])->name('rooms.quick-status');
    // Room photo deletion (admin + desk_staff)
    Route::delete('/rooms/{room}/photos/{photo}', [AdminRoomController::class, 'destroyPhoto'])->name('rooms.photos.destroy');

    // Housekeeping Logs
    Route::resource('housekeeping', HousekeepingController::class)->only(['index', 'store', 'destroy']);

    // Customer History
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{user}', [CustomerController::class, 'show'])->name('customers.show');

    // Admin-only Management
    Route::middleware('role:admin')->group(function () {
        Route::resource('rooms', AdminRoomController::class);
        Route::resource('discounts', DiscountController::class);
        Route::resource('carousels', CarouselController::class);
        Route::resource('categories', RoomCategoryController::class);
        Route::resource('amenities', AmenityController::class);
        Route::get('/settings', [ResortSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [ResortSettingController::class, 'update'])->name('settings.update');
    });
});
