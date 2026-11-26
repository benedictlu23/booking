<?php

namespace App\Http\Controllers;

use App\Events\BookingCancelled;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        $isDayPass = $request->input('booking_type') === 'day_pass';
        if ($isDayPass && $request->filled('check_in')) {
            $request->merge(['check_out' => $request->input('check_in')]);
        }

        if (auth()->check()) {
            $authUser = auth()->user();
            if (! $request->filled('guest_name')) {
                $request->merge(['guest_name' => ! empty($authUser->name) ? $authUser->name : 'Guest User']);
            }
            if (! $request->filled('guest_email')) {
                $request->merge(['guest_email' => ! empty($authUser->email) ? $authUser->email : 'guest@example.com']);
            }
            if (! $request->filled('guest_phone')) {
                $request->merge(['guest_phone' => ! empty($authUser->phone) ? $authUser->phone : '09123456789']);
            }
        }

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'min:7', 'max:30'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', $isDayPass ? 'same:check_in' : 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'pets' => ['nullable', 'integer', 'min:0'],
            'booking_type' => ['required', 'in:overnight,day_pass'],
            'payment_method' => ['required', 'string'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'discount_code' => ['nullable', 'string'],
            'addons' => ['nullable', 'array'],
            'addons.*.id' => ['nullable', 'exists:room_addon_services,id'],
            'addons.*.quantity' => ['nullable', 'integer', 'min:0'],
        ], [
            'guest_phone.required' => 'Please provide a valid phone number for booking updates.',
            'check_out.same' => 'For Day Pass, the Check-Out date must be the same as the Check-In date.',
            'check_out.after' => 'Check-out date must be after check-in date for overnight stays.',
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

        // Calculate addons total (scoped to this room with max quantity limits)
        $addonsTotal = 0;
        $selectedAddons = [];
        $scopedAddons = $room->addonServices()->exists()
            ? $room->addonServices()->where('is_active', true)->get()->keyBy('id')
            : AddonService::where('is_active', true)->get()->keyBy('id');

        if (! empty($validated['addons'])) {
            foreach ($validated['addons'] as $addonData) {
                $qty = (int) ($addonData['quantity'] ?? 0);
                if ($qty <= 0 || empty($addonData['id'])) {
                    continue;
                }
                $addonId = (int) $addonData['id'];
                if (! isset($scopedAddons[$addonId])) {
                    return back()->withErrors(['addons' => 'One or more selected add-ons are not available for this room.'])->withInput();
                }

                $addon = $scopedAddons[$addonId];
                $rawLimit = $addon->pivot->max_quantity ?? null;
                if (! empty($rawLimit) && (int) $rawLimit > 0 && $qty > (int) $rawLimit) {
                    return back()->withErrors(['addons' => "Maximum quantity for {$addon->name} is {$rawLimit}."])->withInput();
                }

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

        // If user is authenticated and missing phone_number or name, update user profile safely
        if ($user) {
            $userUpdates = [];
            if (Schema::hasColumn('users', 'phone_number') && empty($user->phone_number) && filled($validated['guest_phone'])) {
                $userUpdates['phone_number'] = $validated['guest_phone'];
            }
            if (empty($user->name) && filled($validated['guest_name'])) {
                $userUpdates['name'] = $validated['guest_name'];
            }
            if (! empty($userUpdates)) {
                $user->update($userUpdates);
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
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in' => Carbon::parse($validated['check_in'])->startOfDay(),
            'check_out' => Carbon::parse($validated['check_out'])->startOfDay(),
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
            $booking->load(['room.category']);
            broadcast(new NewBookingReceived($booking));
        } catch (\Throwable $e) {
            Log::warning('NewBookingReceived broadcast error: '.$e->getMessage());
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

        try {
            broadcast(new BookingCancelled($booking))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('BookingCancelled broadcast error: '.$e->getMessage());
        }

        return redirect()->route('customer.bookings')
            ->with('success', "Booking {$booking->booking_code} has been cancelled.");
    }

    public function checkAvailability(Request $request, Room $room): JsonResponse
    {
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $bookingType = $request->get('booking_type', 'overnight');

        if ($bookingType === 'day_pass' && $checkIn) {
            $checkOut = $checkIn;
        }

        if (! $checkIn || ! $checkOut) {
            return response()->json(['available' => false, 'message' => 'Please select check-in and check-out dates.']);
        }

        try {
            $cIn = Carbon::parse($checkIn);
            $cOut = Carbon::parse($checkOut);

            if ($cIn->lt(Carbon::today())) {
                return response()->json(['available' => false, 'message' => 'Check-in date cannot be in the past.']);
            }

            if ($bookingType !== 'day_pass' && $cOut->lte($cIn)) {
                return response()->json(['available' => false, 'message' => 'Check-out date must be after check-in.']);
            }

            $available = $room->isAvailableFor($cIn, $cOut);
            $nights = $bookingType === 'day_pass' ? 1 : max(1, $cIn->diffInDays($cOut));

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
            // Do NOT auto-mark as paid — admin must verify the uploaded proof.
            // Payment status remains unpaid/partial until admin confirms.
        }

        $booking->update($updateData);

        return back()->with('success', 'Payment proof submitted successfully! Our staff will verify your reservation.');
    }
}
