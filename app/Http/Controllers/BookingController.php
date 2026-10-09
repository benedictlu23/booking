<?php

namespace App\Http\Controllers;

use App\Events\NewBookingReceived;
use App\Models\AddonService;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Discount;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(Room $room, Request $request): View
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
            'payment_method' => ['nullable', 'string', 'in:cash,bank_transfer,gcash'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'discount_code' => ['nullable', 'string'],
            'addons' => ['nullable', 'array'],
            'addons.*.id' => ['nullable', 'exists:room_addon_services,id'],
            'addons.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        if (! $room->isAvailableFor(
            Carbon::parse($validated['check_in']),
            Carbon::parse($validated['check_out'])
        )) {
            return back()->withErrors(['room_id' => 'Sorry, this room is already booked or unavailable for the selected dates.'])->withInput();
        }

        $user = auth()->user();
        $nights = Carbon::parse($validated['check_in'])->diffInDays(Carbon::parse($validated['check_out']));
        $nights = max(1, $nights);

        // Base price calculation (from server only)
        $basePrice = (float) $room->base_price * $nights;
        $petFeeTotal = ((int) ($validated['pets'] ?? 0)) > 0 ? ((float) $room->pet_fee * $nights * (int) $validated['pets']) : 0;

        // Calculate addons total (strictly optional, server-driven pricing)
        $addonsTotal = 0;
        $selectedAddons = [];
        if (! empty($validated['addons'])) {
            foreach ($validated['addons'] as $addonData) {
                $qty = (int) ($addonData['quantity'] ?? 0);
                if ($qty <= 0 || empty($addonData['id'])) {
                    continue;
                }
                $addon = AddonService::find($addonData['id']);
                if ($addon && $addon->is_active) {
                    $multiplier = match ($addon->pricing_type) {
                        'per_night' => $nights,
                        'per_person' => (int) $validated['adults'] + (int) ($validated['children'] ?? 0),
                        default => 1,
                    };
                    $subtotal = round((float) $addon->price * $qty * $multiplier, 2);
                    $addonsTotal += $subtotal;
                    $selectedAddons[] = [
                        'addon_service_id' => $addon->id,
                        'item_name' => $addon->name,
                        'quantity' => $qty,
                        'unit_price' => (float) $addon->price,
                        'subtotal' => $subtotal,
                    ];
                }
            }
        }

        // Discount calculation with runtime safety
        $discountAmount = 0;
        $discountId = null;
        if (! empty($validated['discount_code'])) {
            $discount = Discount::where('code', strtoupper(trim($validated['discount_code'])))->first();
            if ($discount && $discount->isValidNow()) {
                $discountAmount = $discount->calculateDiscount($basePrice + $addonsTotal + $petFeeTotal);
                $discountId = $discount->id;
                $discount->increment('used_count');
            } else {
                return back()->withErrors(['discount_code' => 'Invalid or expired discount code.'])->withInput();
            }
        }

        $totalPrice = max(0, round($basePrice + $addonsTotal + $petFeeTotal - $discountAmount, 2));

        $booking = Booking::create([
            'booking_code' => 'AGRO-'.strtoupper(Str::random(8)),
            'room_id' => $room->id,
            'user_id' => $user?->id,
            'discount_id' => $discountId,
            'guest_name' => $user?->name ?? $request->input('guest_name', 'Guest'),
            'guest_email' => $user?->email ?? $request->input('guest_email'),
            'guest_phone' => $user?->phone_number ?? $request->input('guest_phone'),
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'booking_type' => $validated['booking_type'],
            'adults' => (int) $validated['adults'],
            'children' => (int) ($validated['children'] ?? 0),
            'pets' => (int) ($validated['pets'] ?? 0),
            'base_price' => $basePrice,
            'addons_total' => $addonsTotal,
            'pet_fee_total' => $petFeeTotal,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'is_walk_in' => false,
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Save booking addons
        foreach ($selectedAddons as $addonData) {
            BookingAddon::create(['booking_id' => $booking->id, ...$addonData]);
        }

        // Broadcast to admin panel
        try {
            broadcast(new NewBookingReceived($booking));
        } catch (\Throwable) {
            // Reverb broadcast failure is non-fatal
        }

        return redirect()->route('booking.confirmation', $booking)
            ->with('success', "Booking {$booking->booking_code} confirmed!");
    }

    public function confirmation(Booking $booking): View
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

    public function checkAvailability(Request $request, Room $room): JsonResponse
    {
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        if (! $checkIn || ! $checkOut) {
            return response()->json(['available' => false, 'message' => 'Please select check-in and check-out dates.']);
        }

        try {
            $cIn = Carbon::parse($checkIn);
            $cOut = Carbon::parse($checkOut);

            if ($cIn->lt(Carbon::today()) || $cOut->lte($cIn)) {
                return response()->json(['available' => false, 'message' => 'Check-out date must be after check-in.']);
            }

            $available = $room->isAvailableFor($cIn, $cOut);
            $nights = max(1, $cIn->diffInDays($cOut));

            return response()->json([
                'available' => $available,
                'nights' => $nights,
                'base_total' => round((float) $room->base_price * $nights, 2),
                'message' => $available ? 'Room is available!' : 'Room is already booked for these dates.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['available' => false, 'message' => 'Invalid dates.']);
        }
    }

    public function validateDiscount(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->get('code', '')));
        $amount = (float) $request->get('amount', 0);

        if (empty($code)) {
            return response()->json(['valid' => false, 'message' => 'Please enter a code.']);
        }

        $discount = Discount::where('code', $code)->first();

        if (! $discount || ! $discount->isValidNow()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired discount code.']);
        }

        $minAmount = (float) ($discount->minimum_amount ?? 0);
        if ($minAmount > 0 && $amount < $minAmount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum spend of ₱'.number_format($minAmount, 2).' required for this promo code.',
            ]);
        }

        $discountAmount = $discount->calculateDiscount($amount);

        return response()->json([
            'valid' => true,
            'code' => $discount->code,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'discount_amount' => $discountAmount,
            'message' => "Promo code '{$discount->code}' applied successfully!",
        ]);
    }

    public function submitPaymentProof(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id && auth()->id() !== $booking->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,gcash'],
            'payment_proof' => ['nullable', 'image', 'max:5120'],
        ]);

        $updateData = [
            'payment_method' => $validated['payment_method'],
        ];

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payments/'.$booking->id, 'public');
            $updateData['payment_proof'] = $path;
            $updateData['payment_status'] = 'paid';
        }

        $booking->update($updateData);

        return back()->with('success', 'Payment proof submitted successfully! Our staff will verify your reservation.');
    }
}
