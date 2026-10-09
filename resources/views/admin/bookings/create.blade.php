<x-layouts.admin>
    <x-slot:heading>New Walk-In Booking</x-slot:heading>
    <x-slot:subheading>Register a guest at the front desk</x-slot:subheading>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-6">
            @csrf

            {{-- Room Selection --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Room Selection</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Room *</label>
                        <select name="room_id" id="room_id" required
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('room_id') border-red-400 @enderror">
                            <option value="">— Select a Room —</option>
                            @foreach($rooms->groupBy(fn($r) => $r->category->name) as $categoryName => $categoryRooms)
                                <optgroup label="{{ $categoryName }}">
                                    @foreach($categoryRooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}
                                                data-price="{{ $room->base_price }}" data-pet-fee="{{ $room->pet_fee }}">
                                            {{ $room->room_number }} — {{ $room->name }} (₱{{ number_format($room->base_price, 2) }}/night)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('room_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Booking Type *</label>
                        <select name="booking_type" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="overnight">Overnight Stay</option>
                            <option value="day_pass">Day Pass</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Check-In Date & Time *</label>
                        <input type="datetime-local" name="check_in" value="{{ old('check_in', now()->format('Y-m-d\TH:i')) }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Check-Out Date & Time *</label>
                        <input type="datetime-local" name="check_out" value="{{ old('check_out', now()->addDay()->format('Y-m-d\TH:i')) }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Guest Info --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Guest Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Guest Name *</label>
                        <input type="text" name="guest_name" value="{{ old('guest_name') }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Phone</label>
                        <input type="tel" name="guest_phone" value="{{ old('guest_phone') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                        <input type="email" name="guest_email" value="{{ old('guest_email') }}"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Linked Account</label>
                        <select name="user_id" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">— Walk-in Guest (no account) —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('user_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Guests Breakdown --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <h3 class="font-bold text-stone-900 mb-4">Guest Breakdown</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Adults *</label>
                        <input type="number" name="adults" value="{{ old('adults', 1) }}" min="1" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Children</label>
                        <input type="number" name="children" value="{{ old('children', 0) }}" min="0"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1.5">Pets 🐾</label>
                        <input type="number" name="pets" value="{{ old('pets', 0) }}" min="0"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-lg shadow-emerald-200">
                    ✅ Create Booking
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="px-6 py-3 border border-stone-200 text-stone-600 rounded-xl text-sm hover:bg-stone-50 transition-colors">
                    Cancel
                </a>
            </div>

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="text-sm text-red-700">• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>
    </div>
</x-layouts.admin>
