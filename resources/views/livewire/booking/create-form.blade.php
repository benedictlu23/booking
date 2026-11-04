<?php

use App\Events\NewBookingReceived;
use App\Models\AddonService;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Discount;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public Room $room;
    public string $checkIn = '';
    public string $checkOut = '';
    public string $bookingType = 'overnight';
    public int $adults = 1;
    public int $children = 0;
    public int $pets = 0;
    public string $paymentMethod = 'cash';
    public string $discountCode = '';
    public ?int $discountId = null;
    public float $discountAmount = 0.0;
    public ?string $discountMessage = null;
    public bool $discountValid = false;
    public string $specialRequests = '';
    public array $addonQuantities = [];
    public bool $isAvailable = true;
    public ?string $nextAvailableDate = null;

    public function mount(Room $room, ?string $initialCheckIn = null, ?string $initialCheckOut = null): void
    {
        $this->room = $room->loadMissing(['category', 'amenities', 'photos']);
        $explicitCheckIn = $initialCheckIn ?: request('check_in');
        $this->checkIn = $explicitCheckIn ?: today()->format('Y-m-d');
        $this->checkOut = $initialCheckOut ?: request('check_out', today()->addDay()->format('Y-m-d'));
        $this->adults = max(1, (int) request('adults', 1));
        $this->children = max(0, (int) request('children', 0));
        $this->pets = max(0, (int) request('pets', 0));
        // Constrain booking type to room's allowed types
        $requestedType = request('booking_type', 'overnight');
        $this->bookingType = match ($room->booking_type) {
            'overnight' => 'overnight',
            'day_pass'  => 'day_pass',
            default     => in_array($requestedType, ['overnight', 'day_pass']) ? $requestedType : 'overnight',
        };
        $this->paymentMethod = request('payment_method', 'cash');

        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $this->checkIn;
        }

        $this->checkAvailability();

        // If no explicit date was requested and today is unavailable, auto-advance to next available date
        if (! $explicitCheckIn && ! $this->isAvailable) {
            $nextDate = $this->room->findNextAvailableDate(today());
            if ($nextDate) {
                $this->checkIn = $nextDate->format('Y-m-d');
                $this->checkOut = $this->bookingType === 'day_pass' ? $this->checkIn : $nextDate->copy()->addDay()->format('Y-m-d');
                $this->checkAvailability();
            }
        }
    }

    public function updatedBookingType(): void
    {
        $this->resetErrorBag(['checkIn', 'checkOut', 'bookingType']);
        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $this->checkIn;
        } else {
            $cIn = Carbon::hasFormat($this->checkIn, 'Y-m-d') ? Carbon::parse($this->checkIn) : today();
            $cOut = Carbon::hasFormat($this->checkOut, 'Y-m-d') ? Carbon::parse($this->checkOut) : today()->addDay();
            if ($cOut->lte($cIn)) {
                $this->checkOut = $cIn->copy()->addDay()->format('Y-m-d');
            }
        }
        $this->checkAvailability();
        $this->recalculateDiscount();
    }

    public function updatedCheckIn(): void
    {
        $this->resetErrorBag('checkIn');
        $this->validateDates();
        $this->checkAvailability();
        $this->recalculateDiscount();
    }

    public function updatedCheckOut(): void
    {
        $this->resetErrorBag('checkOut');
        $this->validateDates();
        $this->checkAvailability();
        $this->recalculateDiscount();
    }

    public function updatedAdults(): void
    {
        $this->recalculateDiscount();
    }

    public function updatedChildren(): void
    {
        $this->recalculateDiscount();
    }

    public function updatedPets(): void
    {
        $this->recalculateDiscount();
    }

    protected function validateDates(): void
    {
        $cIn = Carbon::hasFormat($this->checkIn, 'Y-m-d') ? Carbon::parse($this->checkIn) : today();
        if ($this->bookingType === 'day_pass') {
            $this->checkOut = $cIn->format('Y-m-d');
            return;
        }

        $cOut = Carbon::hasFormat($this->checkOut, 'Y-m-d') ? Carbon::parse($this->checkOut) : today()->addDay();

        if ($cOut->lte($cIn)) {
            $this->checkOut = $cIn->copy()->addDay()->format('Y-m-d');
        }
    }

    public function checkAvailability(): void
    {
        try {
            $cIn = Carbon::parse($this->checkIn);
            $cOut = Carbon::parse($this->checkOut);
            $this->isAvailable = $this->room->isAvailableFor($cIn, $cOut);
            if (! $this->isAvailable) {
                $next = $this->room->findNextAvailableDate($cIn->copy()->addDay());
                $this->nextAvailableDate = $next ? $next->format('Y-m-d') : null;
            } else {
                $this->nextAvailableDate = null;
            }
        } catch (\Throwable) {
            $this->isAvailable = true;
            $this->nextAvailableDate = null;
        }
    }

    public function selectDate(string $date): void
    {
        $this->checkIn = $date;
        $this->updatedCheckIn();
    }

    public function getAvailableAddonsProperty(): EloquentCollection
    {
        return AddonService::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function getNightsProperty(): int
    {
        if ($this->bookingType === 'day_pass') {
            return 1;
        }

        try {
            $cIn = Carbon::parse($this->checkIn);
            $cOut = Carbon::parse($this->checkOut);

            return max(1, $cIn->diffInDays($cOut));
        } catch (\Throwable) {
            return 1;
        }
    }

    public function getBaseTotalProperty(): float
    {
        return round((float) $this->room->base_price * $this->nights, 2);
    }

    public function getPetTotalProperty(): float
    {
        if ($this->pets <= 0 || ! $this->room->allows_pets) {
            return 0.0;
        }
        return round((float) $this->room->pet_fee * $this->nights * $this->pets, 2);
    }

    public function getAddonsTotalProperty(): float
    {
        $total = 0.0;
        $addons = $this->availableAddons->keyBy('id');

        foreach ($this->addonQuantities as $addonId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0 || ! isset($addons[$addonId])) {
                continue;
            }
            $addon = $addons[$addonId];
            $multiplier = match ($addon->pricing_type) {
                'per_night' => $this->nights,
                'per_person' => $this->adults + $this->children,
                default => 1,
            };
            $total += (float) $addon->price * $qty * $multiplier;
        }

        return round($total, 2);
    }

    public function getSubtotalProperty(): float
    {
        return round($this->baseTotal + $this->petTotal + $this->addonsTotal, 2);
    }

    public function getTotalPriceProperty(): float
    {
        return max(0.0, round($this->subtotal - $this->discountAmount, 2));
    }

    public function applyDiscount(): void
    {
        $code = strtoupper(trim($this->discountCode));
        if (empty($code)) {
            $this->discountId = null;
            $this->discountAmount = 0.0;
            $this->discountValid = false;
            $this->discountMessage = null;
            return;
        }

        $discount = Discount::where('code', $code)->first();

        if (! $discount || ! $discount->isValidNow()) {
            $this->discountId = null;
            $this->discountAmount = 0.0;
            $this->discountValid = false;
            $this->discountMessage = 'Invalid or expired discount code.';
            return;
        }

        if ($discount->minimum_amount && $this->subtotal < (float) $discount->minimum_amount) {
            $this->discountId = null;
            $this->discountAmount = 0.0;
            $this->discountValid = false;
            $this->discountMessage = "Minimum spend of ₱".number_format($discount->minimum_amount, 2)." required.";
            return;
        }

        $this->discountId = $discount->id;
        $this->discountAmount = $discount->calculateDiscount($this->subtotal);
        $this->discountValid = true;
        $this->discountMessage = "Promo '{$discount->code}' applied: ₱".number_format($this->discountAmount, 2)." off!";
    }

    public function recalculateDiscount(): void
    {
        if ($this->discountValid && $this->discountId) {
            $discount = Discount::find($this->discountId);
            if ($discount) {
                if ($discount->minimum_amount && $this->subtotal < (float) $discount->minimum_amount) {
                    $this->discountId = null;
                    $this->discountAmount = 0.0;
                    $this->discountValid = false;
                    $this->discountMessage = "Minimum spend of ₱".number_format($discount->minimum_amount, 2)." required.";
                } else {
                    $this->discountAmount = $discount->calculateDiscount($this->subtotal);
                }
            }
        }
    }

    public function setAddonQty(int $addonId, int $qty): void
    {
        if ($qty <= 0) {
            unset($this->addonQuantities[$addonId]);
        } else {
            $this->addonQuantities[$addonId] = min(10, $qty);
        }
        $this->recalculateDiscount();
    }

    public function submitBooking()
    {
        $isDayPass = $this->bookingType === 'day_pass';
        if ($isDayPass) {
            $this->checkOut = $this->checkIn;
        }

        $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', $isDayPass ? 'same:checkIn' : 'after:checkIn'],
            'adults' => ['required', 'integer', 'min:1', 'max:'.$this->room->capacity_adults],
            'children' => ['nullable', 'integer', 'min:0'],
            'pets' => ['nullable', 'integer', 'min:0'],
            'bookingType' => ['required', 'in:overnight,day_pass'],
            'paymentMethod' => ['required', 'in:cash,bank_transfer,gcash'],
            'specialRequests' => ['nullable', 'string', 'max:1000'],
        ], [
            'checkOut.same' => 'For Day Pass, the Check-Out date must be the same as the Check-In date.',
            'checkOut.after' => 'Check-out date must be after check-in date for overnight stays.',
        ]);

        $cIn = Carbon::parse($this->checkIn);
        $cOut = Carbon::parse($this->checkOut);

        if (! $this->room->isAvailableFor($cIn, $cOut)) {
            $this->isAvailable = false;
            $this->addError('checkIn', 'This room is unavailable for the selected dates.');
            return;
        }

        $user = auth()->user();

        $booking = Booking::create([
            'booking_code' => 'AGRO-'.strtoupper(Str::random(8)),
            'room_id' => $this->room->id,
            'user_id' => $user?->id,
            'discount_id' => $this->discountValid ? $this->discountId : null,
            'guest_name' => $user?->name ?? 'Guest',
            'guest_email' => $user?->email,
            'guest_phone' => $user?->phone_number,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'booking_type' => $this->bookingType,
            'adults' => $this->adults,
            'children' => $this->children,
            'pets' => $this->pets,
            'base_price' => $this->baseTotal,
            'addons_total' => $this->addonsTotal,
            'pet_fee_total' => $this->petTotal,
            'discount_amount' => $this->discountAmount,
            'total_price' => $this->totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => $this->paymentMethod,
            'is_walk_in' => false,
            'special_requests' => $this->specialRequests ?: null,
        ]);

        $addons = $this->availableAddons->keyBy('id');
        foreach ($this->addonQuantities as $addonId => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && isset($addons[$addonId])) {
                $addon = $addons[$addonId];
                $multiplier = match ($addon->pricing_type) {
                    'per_night' => $this->nights,
                    'per_person' => $this->adults + $this->children,
                    default => 1,
                };
                BookingAddon::create([
                    'booking_id' => $booking->id,
                    'addon_service_id' => $addon->id,
                    'item_name' => $addon->name,
                    'quantity' => $qty,
                    'unit_price' => (float) $addon->price,
                    'subtotal' => round((float) $addon->price * $qty * $multiplier, 2),
                ]);
            }
        }

        if ($this->discountValid && $this->discountId) {
            Discount::where('id', $this->discountId)->increment('used_count');
        }

        try {
            broadcast(new NewBookingReceived($booking));
        } catch (\Throwable) {}

        return redirect()->route('booking.confirmation', $booking)
            ->with('success', "Booking {$booking->booking_code} confirmed!");
    }
}; ?>

<div>
    <form wire:submit="submitBooking" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Form --}}
        <div class="lg:col-span-2 space-y-5">

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm">
                    <p class="font-bold flex items-center gap-1.5 mb-1.5">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Please resolve the following before continuing:
                    </p>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Stay Dates --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-stone-900 text-base">Your Stay</h2>
                    <div wire:loading.remove wire:target="checkIn, checkOut, bookingType">
                        @if($isAvailable)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg flex items-center gap-1">
                                ✓ Available for selected dates
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-semibold rounded-lg flex items-center gap-1">
                                ⚠️ Not Available
                            </span>
                        @endif
                    </div>
                    <div wire:loading wire:target="checkIn, checkOut, bookingType" class="text-xs text-stone-400">
                        ⏳ Checking availability...
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Check-In</label>
                        <input type="date"
                               wire:model.live="checkIn"
                               min="{{ today()->format('Y-m-d') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('checkIn') border-red-400 @enderror">
                        @error('checkIn')
                            <span class="text-xs text-red-600 mt-1 block font-medium">⚠️ {{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                            <span>Check-Out</span>
                            @if($bookingType === 'day_pass')
                                <span class="text-[10px] text-amber-700 font-semibold normal-case bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">Locked (Day Pass)</span>
                            @endif
                        </label>
                        <input type="date"
                               wire:model.live="checkOut"
                               @disabled($bookingType === 'day_pass')
                               @readonly($bookingType === 'day_pass')
                               min="{{ $bookingType === 'day_pass' ? $checkIn : today()->addDay()->format('Y-m-d') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('checkOut') border-red-400 @enderror {{ $bookingType === 'day_pass' ? 'bg-stone-100 text-stone-500 cursor-not-allowed select-none' : '' }}">
                        @if($bookingType === 'day_pass')
                            <p class="text-[11px] text-stone-400 mt-1">Same-day departure for Day Pass bookings.</p>
                        @endif
                        @error('checkOut')
                            <span class="text-xs text-red-600 mt-1 block font-medium">⚠️ {{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Booking Type</label>
                        @if($room->booking_type === 'overnight')
                            <div class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-stone-50 text-stone-600">
                                Overnight Stay <span class="text-xs text-stone-400">(only option for this room)</span>
                            </div>
                        @elseif($room->booking_type === 'day_pass')
                            <div class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-stone-50 text-stone-600">
                                Day Pass <span class="text-xs text-stone-400">(only option for this room)</span>
                            </div>
                        @else
                            <select wire:model.live="bookingType"
                                    class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="overnight">Overnight Stay</option>
                                <option value="day_pass">Day Pass</option>
                            </select>
                        @endif
                        @error('bookingType')
                            <span class="text-xs text-red-600 mt-1 block font-medium">⚠️ {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                @if(!$isAvailable)
                    <div class="mt-3 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                This room is not available for the selected dates.
                            </p>
                            @if($nextAvailableDate)
                                <p class="text-stone-600 mt-1">Next available opening: <span class="font-bold text-stone-900">{{ \Illuminate\Support\Carbon::parse($nextAvailableDate)->format('D, M d, Y') }}</span></p>
                            @endif
                        </div>
                        @if($nextAvailableDate)
                            <button type="button"
                                    wire:click="selectDate('{{ $nextAvailableDate }}')"
                                    class="shrink-0 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition-colors shadow-sm flex items-center gap-1">
                                <span>Switch to {{ \Illuminate\Support\Carbon::parse($nextAvailableDate)->format('M d') }}</span> →
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Guests --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h2 class="font-bold text-stone-900 mb-4 text-base">Guests</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Adults</label>
                        <input type="number"
                               wire:model.live="adults"
                               min="1" max="{{ $room->capacity_adults }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="text-xs text-stone-400 mt-1">Max {{ $room->capacity_adults }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Children</label>
                        <input type="number"
                               wire:model.live="children"
                               min="0" max="{{ $room->capacity_children }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    @if($room->allows_pets)
                        <div>
                            <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1.5">Pets 🐾</label>
                            <input type="number"
                                   wire:model.live="pets"
                                   min="0" max="5"
                                   class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <p class="text-xs text-amber-600 mt-1">+₱{{ number_format($room->pet_fee, 0) }}/night each</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Optional Add-ons --}}
            @if($this->availableAddons->isNotEmpty())
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                    <h2 class="font-bold text-stone-900 mb-1 text-base">Enhance Your Stay</h2>
                    <p class="text-sm text-stone-500 mb-4">Add optional extras to your booking</p>
                    <div class="space-y-3">
                        @foreach($this->availableAddons as $addon)
                            @php
                                $qty = $addonQuantities[$addon->id] ?? 0;
                            @endphp
                            <div class="flex items-center gap-4 p-3 border rounded-xl transition-colors {{ $qty > 0 ? 'border-emerald-300 bg-emerald-50/20' : 'border-stone-100 hover:border-emerald-200' }}">
                                <span class="text-2xl">{{ $addon->icon ?? '✨' }}</span>
                                <div class="flex-1">
                                    <p class="font-semibold text-stone-900 text-sm">{{ $addon->name }}</p>
                                    @if($addon->description)
                                        <p class="text-xs text-stone-500">{{ $addon->description }}</p>
                                    @endif
                                    <p class="text-xs text-emerald-600 font-semibold mt-0.5">
                                        ₱{{ number_format($addon->price, 2) }} / {{ str_replace('_', ' ', $addon->pricing_type) }}
                                    </p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <button type="button"
                                            wire:click="setAddonQty({{ $addon->id }}, {{ max(0, $qty - 1) }})"
                                            class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-sm flex items-center justify-center transition-colors">
                                        -
                                    </button>
                                    <span class="w-6 text-center font-bold text-sm text-stone-900">{{ $qty }}</span>
                                    <button type="button"
                                            wire:click="setAddonQty({{ $addon->id }}, {{ $qty + 1 }})"
                                            class="w-8 h-8 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-sm flex items-center justify-center transition-colors">
                                        +
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Payment Method Option --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h2 class="font-bold text-stone-900 mb-4 text-base">Payment Method</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer transition-colors {{ $paymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="cash" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="font-semibold text-xs text-stone-900">Cash on Arrival</p>
                            <p class="text-[11px] text-stone-500">Pay at Front Desk</p>
                        </div>
                    </label>
                    <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer transition-colors {{ $paymentMethod === 'gcash' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="gcash" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="font-semibold text-xs text-stone-900">GCash</p>
                            <p class="text-[11px] text-stone-500">Online transfer</p>
                        </div>
                    </label>
                    <label class="p-3 border rounded-xl flex items-center gap-3 cursor-pointer transition-colors {{ $paymentMethod === 'bank_transfer' ? 'border-emerald-500 bg-emerald-50/50' : 'border-stone-200' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="bank_transfer" class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="font-semibold text-xs text-stone-900">Bank Transfer</p>
                            <p class="text-[11px] text-stone-500">BDO Account</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Discount Code --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h2 class="font-bold text-stone-900 mb-4 text-base">Discount Code</h2>
                <div class="flex gap-3">
                    <input type="text"
                           wire:model="discountCode"
                           wire:keydown.enter.prevent="applyDiscount"
                           placeholder="Enter promo code (e.g. PROMO500)..."
                           class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase">
                    <button type="button"
                            wire:click="applyDiscount"
                            class="px-5 py-2.5 bg-stone-800 hover:bg-stone-900 text-white text-sm font-semibold rounded-xl transition-colors">
                        Apply
                    </button>
                </div>
                @if($discountMessage)
                    <p class="mt-2 text-xs font-semibold {{ $discountValid ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $discountMessage }}
                    </p>
                @endif
            </div>

            {{-- Special Requests --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h2 class="font-bold text-stone-900 mb-4 text-base">Special Requests</h2>
                <textarea wire:model="specialRequests"
                          rows="3"
                          placeholder="Any special requests or notes? (optional)"
                          class="w-full px-4 py-3 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
            </div>
        </div>

        {{-- Right: Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl shadow-stone-200/50 border border-stone-100 p-6 sticky top-24">
                <h2 class="font-bold text-stone-900 mb-4 text-base">Booking Summary</h2>

                <div class="mb-4 p-3 bg-stone-50 rounded-xl">
                    <p class="font-semibold text-stone-900 text-sm">{{ $room->name }}</p>
                    <p class="text-xs text-stone-500">Room {{ $room->room_number }} · {{ $room->category?->name }}</p>
                </div>

                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between text-stone-600">
                        <span>₱{{ number_format($room->base_price, 0) }} × {{ $this->nights }} night{{ $this->nights !== 1 ? 's' : '' }}</span>
                        <span>₱{{ number_format($this->baseTotal, 2) }}</span>
                    </div>

                    @if($this->petTotal > 0)
                        <div class="flex justify-between text-amber-700">
                            <span>Pet fee × {{ $this->nights }} night{{ $this->nights !== 1 ? 's' : '' }} ({{ $pets }} pet{{ $pets > 1 ? 's' : '' }})</span>
                            <span>₱{{ number_format($this->petTotal, 2) }}</span>
                        </div>
                    @endif

                    @if($this->addonsTotal > 0)
                        <div class="flex justify-between text-stone-600">
                            <span>Add-ons</span>
                            <span>₱{{ number_format($this->addonsTotal, 2) }}</span>
                        </div>
                    @endif

                    @if($discountAmount > 0)
                        <div class="flex justify-between text-emerald-600 font-medium">
                            <span>Discount</span>
                            <span>-₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-stone-100 pt-3 flex justify-between font-black text-lg">
                        <span class="text-stone-900">Total</span>
                        <span class="text-emerald-600">₱{{ number_format($this->totalPrice, 2) }}</span>
                    </div>
                </div>

                <button type="submit"
                        @if(!$isAvailable) disabled @endif
                        class="w-full py-3.5 text-white font-bold rounded-2xl transition-all shadow-lg shadow-emerald-200 text-sm {{ $isAvailable ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 hover:-translate-y-0.5' : 'bg-stone-300 cursor-not-allowed opacity-60' }}">
                    <span>Confirm Booking →</span>
                </button>
                <p class="text-xs text-stone-400 text-center mt-2">Payment due at check-in or via proof upload.</p>
            </div>
        </div>
    </form>
</div>
