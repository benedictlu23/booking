<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\ResortSettingController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\RoomSearchController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::get('/', [RoomSearchController::class, 'index'])->name('home');
Route::get('/rooms', [RoomSearchController::class, 'search'])->name('rooms.search');
Route::get('/rooms/{room}', [RoomSearchController::class, 'show'])->name('rooms.show');

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
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
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

    // Room Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('rooms', AdminRoomController::class);
        Route::resource('discounts', DiscountController::class);
        Route::resource('carousels', CarouselController::class);
        Route::get('/settings', [ResortSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [ResortSettingController::class, 'update'])->name('settings.update');
    });
});
