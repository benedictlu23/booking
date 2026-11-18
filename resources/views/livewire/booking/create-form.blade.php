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
    <form wire:submit="submitBooking" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left: Reservation Configuration (8 cols) --}}
        <div class="lg:col-span-8 space-y-7">

            @if($errors->any())
                <div class="p-5 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs">
                    <p class="font-bold flex items-center gap-2 mb-2 text-sm">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Please address the following requirements:
                    </p>
                    <ul class="list-disc list-inside space-y-1 font-medium pl-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. Schedule & Stay Duration --}}
            <div class="bg-white rounded-3xl p-7 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#233D32]/10 pb-4">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Step 1</span>
                        <h2 class="font-serif font-bold text-xl text-[#233D32]">Stay Schedule</h2>
                    </div>
                    <div wire:loading.remove wire:target="checkIn, checkOut, bookingType">
                        @if($isAvailable)
                            <span class="px-3 py-1 bg-[#EFEBE1] text-[#233D32] text-xs font-semibold rounded-full border border-[#233D32]/10 flex items-center gap-1.5 shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#233D32]"></span>
                                <span>✓ Available for selected dates</span>
                            </span>
                        @else
                            <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-semibold rounded-full border border-rose-200 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>⚠️ Not Available</span>
                            </span>
                        @endif
                    </div>
                    <div wire:loading wire:target="checkIn, checkOut, bookingType" class="text-xs text-[#777A72] animate-pulse">
                        Checking calendar...
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Check-In</label>
                        <input type="date"
                               wire:model.live="checkIn"
                               min="{{ today()->format('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] @error('checkIn') border-red-400 @enderror">
                        @error('checkIn')
                            <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72] flex items-center justify-between">
                            <span>Check-Out</span>
                            @if($bookingType === 'day_pass')
                                <span class="text-[10px] text-[#8C734D] font-semibold normal-case bg-[#EFEBE1] px-2 py-0.5 rounded-full border border-[#A68B61]/30">Locked (Day Pass)</span>
                            @endif
                        </label>
                        <input type="date"
                               wire:model.live="checkOut"
                               @disabled($bookingType === 'day_pass')
                               @readonly($bookingType === 'day_pass')
                               min="{{ $bookingType === 'day_pass' ? $checkIn : today()->addDay()->format('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] @error('checkOut') border-red-400 @enderror {{ $bookingType === 'day_pass' ? 'bg-[#EFEBE1] text-[#777A72] cursor-not-allowed select-none opacity-80' : '' }}">
                        @if($bookingType === 'day_pass')
                            <p class="text-[11px] text-[#8C734D] mt-1 font-light">Same-day departure for Day Pass reservations.</p>
                        @endif
                        @error('checkOut')
                            <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Accommodation Type</label>
                        @if($room->booking_type === 'overnight')
                            <div class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium bg-[#EFEBE1] text-[#233D32]">
                                Overnight Stay <span class="text-xs text-[#777A72] font-normal">(Dedicated)</span>
                            </div>
                        @elseif($room->booking_type === 'day_pass')
                            <div class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium bg-[#EFEBE1] text-[#233D32]">
                                Day Pass <span class="text-xs text-[#777A72] font-normal">(Dedicated)</span>
                            </div>
                        @else
                            <select wire:model.live="bookingType"
                                    class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                                <option value="overnight">Overnight Stay</option>
                                <option value="day_pass">Day Pass Only</option>
                            </select>
                        @endif
                        @error('bookingType')
                            <span class="text-xs text-rose-600 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                @if(!$isAvailable)
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                This room is not available for the selected dates.
                            </p>
                            @if($nextAvailableDate)
                                <p class="text-[#595D58] mt-1">Next available check-in opening: <strong class="text-[#233D32]">{{ \Illuminate\Support\Carbon::parse($nextAvailableDate)->format('D, M d, Y') }}</strong></p>
                            @endif
                        </div>
                        @if($nextAvailableDate)
                            <button type="button"
                                    wire:click="selectDate('{{ $nextAvailableDate }}')"
                                    class="shrink-0 px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-wider transition-all shadow-xs flex items-center gap-1.5">
                                <span>Switch to {{ \Illuminate\Support\Carbon::parse($nextAvailableDate)->format('M d') }}</span>
                                <span>→</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 2. Guest Roster & Pet Arrangements --}}
            <div class="bg-white rounded-3xl p-7 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 space-y-5">
                <div class="border-b border-[#233D32]/10 pb-4">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Step 2</span>
                    <h2 class="font-serif font-bold text-xl text-[#233D32]">Guests & Pets</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Adults</label>
                        <input type="number"
                               wire:model.live="adults"
                               min="1" max="{{ $room->capacity_adults }}"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <p class="text-[11px] text-[#777A72] mt-1 font-light">Max {{ $room->capacity_adults }} adults</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Children</label>
                        <input type="number"
                               wire:model.live="children"
                               min="0" max="{{ $room->capacity_children }}"
                               class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <p class="text-[11px] text-[#777A72] mt-1 font-light">Max {{ $room->capacity_children }} children</p>
                    </div>

                    @if($room->allows_pets)
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72]">Pets 🐾</label>
                            <input type="number"
                                   wire:model.live="pets"
                                   min="0" max="5"
                                   class="w-full px-3.5 py-2.5 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                            <p class="text-[11px] text-[#A68B61] mt-1 font-medium">+₱{{ number_format($room->pet_fee, 0) }}/night per pet</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Optional Add-ons --}}
            @if($this->availableAddons->isNotEmpty())
                <div class="bg-white rounded-3xl p-7 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 space-y-5">
                    <div class="border-b border-[#233D32]/10 pb-4">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Step 3</span>
                        <h2 class="font-serif font-bold text-xl text-[#233D32]">Curated Add-On Services</h2>
                        <p class="text-xs text-[#777A72] font-light mt-0.5">Select optional amenities and dining experiences for your stay</p>
                    </div>

                    <div class="space-y-3.5">
                        @foreach($this->availableAddons as $addon)
                            @php
                                $qty = $addonQuantities[$addon->id] ?? 0;
                            @endphp
                            <div class="flex items-center gap-4 p-4 rounded-2xl border transition-all {{ $qty > 0 ? 'border-[#A68B61] bg-[#FAF8F4]' : 'border-[#233D32]/10 hover:border-[#233D32]/30 bg-white' }}">
                                <span class="text-3xl shrink-0">{{ $addon->icon ?? '🌿' }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-serif font-bold text-[#233D32] text-sm leading-snug">{{ $addon->name }}</p>
                                    @if($addon->description)
                                        <p class="text-xs text-[#777A72] font-light mt-0.5 leading-relaxed">{{ $addon->description }}</p>
                                    @endif
                                    <p class="text-xs font-semibold text-[#A68B61] mt-1">
                                        ₱{{ number_format($addon->price, 2) }}
                                        <span class="text-[#777A72] font-normal">/ {{ str_replace('_', ' ', $addon->pricing_type) }}</span>
                                    </p>
                                </div>
                                <div class="shrink-0 flex items-center gap-2">
                                    <button type="button"
                                            wire:click="setAddonQty({{ $addon->id }}, {{ max(0, $qty - 1) }})"
                                            class="w-8 h-8 rounded-lg bg-[#EFEBE1] hover:bg-[#E5E0D4] text-[#233D32] font-bold text-sm flex items-center justify-center transition-colors">
                                        -
                                    </button>
                                    <span class="w-7 text-center font-bold text-sm text-[#233D32]">{{ $qty }}</span>
                                    <button type="button"
                                            wire:click="setAddonQty({{ $addon->id }}, {{ $qty + 1 }})"
                                            class="w-8 h-8 rounded-lg bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-bold text-sm flex items-center justify-center transition-colors shadow-xs">
                                        +
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. Payment Preference --}}
            <div class="bg-white rounded-3xl p-7 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 space-y-5">
                <div class="border-b border-[#233D32]/10 pb-4">
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Step 4</span>
                    <h2 class="font-serif font-bold text-xl text-[#233D32]">Payment Method</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <label class="p-4 border rounded-2xl flex items-start gap-3.5 cursor-pointer transition-all {{ $paymentMethod === 'cash' ? 'border-[#233D32] bg-[#FAF8F4] shadow-xs' : 'border-[#233D32]/15 hover:border-[#233D32]/40' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="cash" class="mt-0.5 text-[#233D32] focus:ring-[#233D32]">
                        <div>
                            <p class="font-semibold text-xs uppercase tracking-wider text-[#233D32]">Cash on Arrival</p>
                            <p class="text-[11px] text-[#777A72] font-light mt-0.5">Settle directly at front desk</p>
                        </div>
                    </label>

                    <label class="p-4 border rounded-2xl flex items-start gap-3.5 cursor-pointer transition-all {{ $paymentMethod === 'gcash' ? 'border-[#233D32] bg-[#FAF8F4] shadow-xs' : 'border-[#233D32]/15 hover:border-[#233D32]/40' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="gcash" class="mt-0.5 text-[#233D32] focus:ring-[#233D32]">
                        <div>
                            <p class="font-semibold text-xs uppercase tracking-wider text-[#233D32]">GCash</p>
                            <p class="text-[11px] text-[#777A72] font-light mt-0.5">Online mobile wallet</p>
                        </div>
                    </label>

                    <label class="p-4 border rounded-2xl flex items-start gap-3.5 cursor-pointer transition-all {{ $paymentMethod === 'bank_transfer' ? 'border-[#233D32] bg-[#FAF8F4] shadow-xs' : 'border-[#233D32]/15 hover:border-[#233D32]/40' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="bank_transfer" class="mt-0.5 text-[#233D32] focus:ring-[#233D32]">
                        <div>
                            <p class="font-semibold text-xs uppercase tracking-wider text-[#233D32]">Bank Transfer</p>
                            <p class="text-[11px] text-[#777A72] font-light mt-0.5">Direct deposit to BDO</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 5. Promotion & Special Requests --}}
            <div class="bg-white rounded-3xl p-7 shadow-[0_4px_24px_-4px_rgba(35,61,50,0.06)] border border-[#233D32]/10 space-y-6">
                <div>
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Step 5</span>
                    <h2 class="font-serif font-bold text-xl text-[#233D32] mb-3">Promo Code & Requests</h2>

                    {{-- Promo Input --}}
                    <div class="flex gap-3">
                        <input type="text"
                               wire:model="discountCode"
                               wire:keydown.enter.prevent="applyDiscount"
                               placeholder="Enter promo coupon..."
                               class="flex-1 px-4 py-2.5 border border-[#233D32]/15 rounded-xl text-sm uppercase tracking-wider font-mono font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        <button type="button"
                                wire:click="applyDiscount"
                                class="px-5 py-2.5 bg-[#EFEBE1] hover:bg-[#233D32] text-[#233D32] hover:text-[#F7F5EF] text-xs font-semibold uppercase tracking-wider rounded-xl transition-all border border-[#233D32]/15 shadow-xs">
                            Apply
                        </button>
                    </div>
                    @if($discountMessage)
                        <p class="mt-2 text-xs font-semibold {{ $discountValid ? 'text-[#233D32]' : 'text-rose-700' }}">
                            {{ $discountMessage }}
                        </p>
                    @endif
                </div>

                <div class="border-t border-[#233D32]/10 pt-5">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-[#777A72] mb-2">Special Requests or Notes</label>
                    <textarea wire:model="specialRequests"
                              rows="3"
                              placeholder="Any dietary requirements, arrival notes, or quiet preferences? (optional)"
                              class="w-full px-4 py-3 border border-[#233D32]/15 rounded-xl text-sm font-medium text-[#292D29] bg-[#FAF8F4] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] resize-none font-light leading-relaxed"></textarea>
                </div>
            </div>
        </div>

        {{-- Right: Resort Folio Summary (4 cols) --}}
        <div class="lg:col-span-4">
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(35,61,50,0.1)] border border-[#233D32]/10 p-7 sticky top-28 space-y-6">
                <div>
                    <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61] block mb-1">Reservation Folio</span>
                    <h2 class="font-serif font-bold text-2xl text-[#233D32]">Booking Summary</h2>
                </div>

                {{-- Accommodation Overview Card --}}
                <div class="p-4 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/10 flex items-center gap-3.5">
                    @if($room->photos->isNotEmpty())
                        <img src="{{ $room->photos->first()->path }}" alt="{{ $room->name }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-white border border-[#233D32]/10 p-1 flex items-center justify-center shrink-0">
                            <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="" class="w-10 h-10 object-contain">
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-serif font-bold text-sm text-[#233D32] truncate">{{ $room->name }}</p>
                        <p class="text-xs text-[#777A72] font-mono">Unit {{ $room->room_number }} · {{ $room->category?->name }}</p>
                    </div>
                </div>

                {{-- Calculations Breakdown --}}
                <div class="space-y-3 text-xs text-[#595D58] border-t border-[#233D32]/10 pt-4">
                    <div class="flex justify-between items-center">
                        <span>Rate: ₱{{ number_format($room->base_price, 0) }} × {{ $this->nights }} night{{ $this->nights !== 1 ? 's' : '' }}</span>
                        <span class="font-semibold text-[#292D29]">₱{{ number_format($this->baseTotal, 2) }}</span>
                    </div>

                    @if($this->petTotal > 0)
                        <div class="flex justify-between items-center text-[#A68B61]">
                            <span>Pet Fee ({{ $pets }} pet{{ $pets > 1 ? 's' : '' }} × {{ $this->nights }} night{{ $this->nights !== 1 ? 's' : '' }})</span>
                            <span class="font-semibold">₱{{ number_format($this->petTotal, 2) }}</span>
                        </div>
                    @endif

                    @if($this->addonsTotal > 0)
                        <div class="flex justify-between items-center">
                            <span>Selected Add-On Services</span>
                            <span class="font-semibold text-[#292D29]">₱{{ number_format($this->addonsTotal, 2) }}</span>
                        </div>
                    @endif

                    @if($discountAmount > 0)
                        <div class="flex justify-between items-center text-[#233D32] font-semibold bg-[#EFEBE1] px-2.5 py-1.5 rounded-lg border border-[#233D32]/10">
                            <span>Applied Promotion</span>
                            <span>-₱{{ number_format($discountAmount, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-[#233D32]/10 pt-4 flex justify-between items-baseline">
                        <span class="font-serif font-bold text-base text-[#233D32]">Estimated Total</span>
                        <span class="font-serif font-bold text-3xl text-[#233D32]">₱{{ number_format($this->totalPrice, 2) }}</span>
                    </div>
                </div>

                <button type="submit"
                        @if(!$isAvailable) disabled @endif
                        class="w-full py-4 text-[#F7F5EF] font-semibold rounded-xl text-xs uppercase tracking-widest transition-all shadow-[0_4px_15px_rgba(35,61,50,0.2)] {{ $isAvailable ? 'bg-[#233D32] hover:bg-[#172A22] hover:-translate-y-0.5' : 'bg-stone-300 cursor-not-allowed opacity-60' }}">
                    <span>Confirm Reservation →</span>
                </button>
                <p class="text-[11px] text-[#777A72] text-center font-light leading-relaxed">
                    No upfront charge required for Cash reservations. Proof upload available after confirmation.
                </p>
            </div>
        </div>
    </form>
</div>
