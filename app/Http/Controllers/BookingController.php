<?php

namespace App\Http\Controllers;

use App\Events\NewBookingReceived;
use App\Models\AddonService;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Discount;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function show(Room $room, Request $request): \Illuminate\View\View
    {
        $room->load(['category', 'amenities', 'photos']);
        $addons = AddonService::where('is_active', true)->orderBy('sort_order')->get();

        $checkIn = $request->get('check_in', today()->format('Y-m-d'));
        $checkOut = $request->get('check_out', today()->addDay()->format('Y-m-d'));

        return view('booking.create', compact('room', 'addons', 'checkIn', 'checkOut'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'pets' => ['nullable', 'integer', 'min:0'],
            'booking_type' => ['required', 'in:overnight,day_pass'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'discount_code' => ['nullable', 'string'],
            'addons' => ['nullable', 'array'],
            'addons.*.id' => ['exists:addon_services,id'],
            'addons.*.quantity' => ['integer', 'min:1'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if (! $room->isAvailableFor(
            Carbon::parse($validated['check_in']),
            Carbon::parse($validated['check_out'])
        )) {
            return back()->withErrors(['room_id' => 'Sorry, this room is already booked for the selected dates.'])->withInput();
        }

        $user = auth()->user();
        $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
        $nights = max(1, $nights);

        // Base price calculation
        $basePrice = $room->base_price * $nights;
        $petFeeTotal = ($validated['pets'] ?? 0) > 0 ? ($room->pet_fee * $nights) : 0;

        // Calculate addons total
        $addonsTotal = 0;
        $selectedAddons = [];
        if (! empty($validated['addons'])) {
            foreach ($validated['addons'] as $addonData) {
                $addon = AddonService::find($addonData['id']);
                if ($addon && $addon->is_active) {
                    $quantity = (int) $addonData['quantity'];
                    $multiplier = match ($addon->pricing_type) {
                        'per_night' => $nights,
                        'per_person' => $validated['adults'] + ($validated['children'] ?? 0),
                        default => 1,
                    };
                    $subtotal = $addon->price * $quantity * $multiplier;
                    $addonsTotal += $subtotal;
                    $selectedAddons[] = [
                        'addon_service_id' => $addon->id,
                        'quantity' => $quantity,
                        'unit_price' => $addon->price,
                        'subtotal' => $subtotal,
                    ];
                }
            }
        }

        // Discount calculation
        $discountAmount = 0;
        $discountId = null;
        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', $validated['discount_code'])->first();
            if ($discount && $discount->isValidNow()) {
                $discountAmount = $discount->calculateDiscount($basePrice + $addonsTotal + $petFeeTotal);
                $discountId = $discount->id;
                $discount->increment('used_count');
            } else {
                return back()->withErrors(['discount_code' => 'Invalid or expired discount code.'])->withInput();
            }
        }

        $totalPrice = $basePrice + $addonsTotal + $petFeeTotal - $discountAmount;

        $booking = Booking::create([
            'booking_code' => 'AGRO-'.strtoupper(Str::random(8)),
            'room_id' => $room->id,
            'user_id' => $user?->id,
            'discount_id' => $discountId,
            'guest_name' => $user?->name ?? $request->guest_name,
            'guest_email' => $user?->email ?? $request->guest_email,
            'guest_phone' => $user?->phone_number ?? $request->guest_phone,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'booking_type' => $validated['booking_type'],
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'pets' => $validated['pets'] ?? 0,
            'base_price' => $basePrice,
            'addons_total' => $addonsTotal,
            'pet_fee_total' => $petFeeTotal,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'is_walk_in' => false,
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Save booking addons
        foreach ($selectedAddons as $addonData) {
            BookingAddon::create(['booking_id' => $booking->id, ...$addonData]);
        }

        // Broadcast to admin panel
        broadcast(new NewBookingReceived($booking))->toOthers();

        return redirect()->route('booking.confirmation', $booking)
            ->with('success', "Booking {$booking->booking_code} confirmed!");
    }

    public function confirmation(Booking $booking): \Illuminate\View\View
    {
        // Ensure the booking belongs to the current user
        if ($booking->user_id && auth()->id() !== $booking->user_id) {
            abort(403);
        }

        $booking->load(['room.category', 'addons.addonService']);

        return view('booking.confirmation', compact('booking'));
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['cancel' => 'This booking cannot be cancelled.']);
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('customer.bookings')
            ->with('success', "Booking {$booking->booking_code} has been cancelled.");
    }
}

