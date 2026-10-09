<x-layouts.admin>
    <x-slot:heading>Reservation Matrix</x-slot:heading>
    <x-slot:subheading>2D Resource Timeline — Real-time room availability grid</x-slot:subheading>

    {{-- Controls --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <form method="GET" action="{{ route('admin.bookings.matrix') }}" class="flex items-center gap-2">
            <input type="date" name="start" value="{{ $startDate->format('Y-m-d') }}"
                   class="px-3 py-2 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <select name="days" onchange="this.form.submit()"
                    class="px-3 py-2 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Days</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Go</button>
        </form>
        <a href="{{ route('admin.bookings.create') }}"
           class="ml-auto px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
            + New Walk-In
        </a>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 p-4 bg-white rounded-xl border border-stone-100 shadow-sm">
        <span class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Legend:</span>
        @php
            $legend = [
                ['color' => 'bg-emerald-100 text-emerald-700', 'label' => 'Available'],
                ['color' => 'bg-red-200 text-red-800', 'label' => 'Occupied / Booked'],
                ['color' => 'bg-amber-200 text-amber-800', 'label' => 'Pending Payment'],
                ['color' => 'bg-blue-200 text-blue-800', 'label' => 'Walk-in'],
                ['color' => 'bg-stone-300 text-stone-700', 'label' => 'Maintenance'],
            ];
        @endphp
        @foreach($legend as $item)
            <div class="flex items-center gap-1.5">
                <div class="w-4 h-4 rounded {{ $item['color'] }}"></div>
                <span class="text-xs text-stone-600">{{ $item['label'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Matrix Grid --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto"
             x-data="{}"
             @booking-received.window="window.location.reload()"
             @booking-updated.window="window.location.reload()">
            <table class="w-full text-xs border-collapse min-w-max">
                {{-- Header --}}
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200">
                        <th class="sticky left-0 z-10 bg-stone-50 p-3 text-left font-semibold text-stone-600 min-w-40 border-r border-stone-200">
                            Room / Unit
                        </th>
                        @foreach($dateRange as $date)
                            <th class="p-2 text-center font-medium min-w-[80px] border-r border-stone-100
                                {{ $date->isToday() ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-stone-500' }}">
                                <div>{{ $date->format('D') }}</div>
                                <div class="{{ $date->isToday() ? 'text-emerald-600' : '' }}">{{ $date->format('M d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- Body --}}
                <tbody>
                    @php $currentCategory = null; @endphp
                    @foreach($rooms as $room)
                        {{-- Category separator --}}
                        @if($room->category_id !== $currentCategory)
                            @php $currentCategory = $room->category_id; @endphp
                            <tr class="bg-stone-800">
                                <td colspan="{{ $dateRange->count() + 1 }}" class="px-4 py-2 text-white font-bold text-xs uppercase tracking-widest">
                                    {{ $room->category->name }}
                                </td>
                            </tr>
                        @endif

                        <tr class="border-b border-stone-100 hover:bg-stone-50/50 transition-colors">
                            {{-- Room label --}}
                            <td class="sticky left-0 z-10 bg-white p-3 border-r border-stone-200">
                                <div class="font-semibold text-stone-900">{{ $room->room_number }}</div>
                                <div class="text-stone-400 text-xs">{{ $room->name }}</div>
                                <div class="mt-1">
                                    @php
                                        $statusBadge = [
                                            'available' => 'bg-emerald-100 text-emerald-700',
                                            'occupied' => 'bg-red-100 text-red-700',
                                            'maintenance' => 'bg-stone-200 text-stone-600',
                                            'housekeeping' => 'bg-purple-100 text-purple-700',
                                            'blocked' => 'bg-stone-300 text-stone-700',
                                        ];
                                    @endphp
                                    <span class="inline-block px-1.5 py-0.5 rounded text-xs font-medium {{ $statusBadge[$room->status] ?? 'bg-stone-100 text-stone-500' }}">
                                        {{ ucfirst($room->status) }}
                                    </span>
                                </div>
                            </td>

                            {{-- Date cells --}}
                            @foreach($dateRange as $date)
                                @php
                                    $booking = $room->bookings->first(function ($b) use ($date) {
                                        return $date->between($b->check_in->startOfDay(), $b->check_out->subSecond()->endOfDay());
                                    });

                                    if ($booking) {
                                        $cellClass = match($booking->status) {
                                            'checked_in' => 'bg-red-200 text-red-900',
                                            'confirmed' => $booking->is_walk_in ? 'bg-blue-200 text-blue-900' : 'bg-red-100 text-red-800',
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            default => 'bg-stone-200 text-stone-600',
                                        };
                                        $cellTitle = $booking->guest_name.' ('.$booking->booking_code.')';
                                    } elseif (in_array($room->status, ['maintenance', 'housekeeping'])) {
                                        $cellClass = 'bg-stone-200 text-stone-500 cursor-not-allowed';
                                        $cellTitle = ucfirst($room->status);
                                    } else {
                                        $cellClass = 'bg-emerald-50 hover:bg-emerald-100 cursor-pointer';
                                        $cellTitle = 'Available — click to book';
                                    }
                                @endphp
                                <td class="p-1 border-r border-stone-100 h-14">
                                    @if($booking)
                                        <a href="{{ route('admin.bookings.show', $booking) }}"
                                           title="{{ $cellTitle }}"
                                           class="block w-full h-full rounded {{ $cellClass }} flex items-center justify-center text-center px-1 leading-tight font-medium truncate transition-colors hover:brightness-95">
                                            @if($date->equalTo($booking->check_in->startOfDay()))
                                                <span class="truncate">{{ Str::limit($booking->guest_name, 8) }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <a href="{{ route('admin.bookings.create', ['room_id' => $room->id, 'check_in' => $date->format('Y-m-d')]) }}"
                                           title="{{ $cellTitle }}"
                                           class="block w-full h-full rounded {{ $cellClass }} transition-colors">
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
