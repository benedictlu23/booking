<x-layouts.admin>
    <x-slot:heading>Bookings</x-slot:heading>
    <x-slot:subheading>Manage all reservations</x-slot:subheading>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.bookings.index') }}"
          class="flex flex-wrap gap-3 mb-6 p-4 bg-white rounded-2xl border border-stone-100 shadow-sm">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code, guest name or email..."
               class="flex-1 min-w-48 px-4 py-2 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <select name="status" class="px-4 py-2 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">All Statuses</option>
            @foreach(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}"
               class="px-4 py-2 border border-stone-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm font-medium rounded-lg hover:bg-stone-700">Filter</button>
        <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 border border-stone-200 text-stone-600 text-sm rounded-lg hover:bg-stone-50">Clear</a>
        <a href="{{ route('admin.bookings.create') }}" class="ml-auto px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
            + Walk-In Booking
        </a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        @foreach(['Booking Code', 'Guest', 'Room', 'Check In', 'Check Out', 'Guests', 'Total', 'Status', 'Payment', 'Actions'] as $header)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="font-mono text-xs text-emerald-600 font-semibold hover:underline">
                                    {{ $booking->booking_code }}
                                </a>
                                @if($booking->is_walk_in)
                                    <span class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded font-medium">Walk-in</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-stone-900">{{ $booking->guest_name }}</div>
                                @if($booking->guest_email)
                                    <div class="text-xs text-stone-400">{{ $booking->guest_email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-medium text-stone-800">{{ $booking->room?->room_number }}</div>
                                <div class="text-xs text-stone-400">{{ $booking->room?->category?->name }}</div>
                            </td>
                            <td class="px-4 py-4 text-stone-600">{{ $booking->check_in->format('M d, Y') }}</td>
                            <td class="px-4 py-4 text-stone-600">{{ $booking->check_out->format('M d, Y') }}</td>
                            <td class="px-4 py-4 text-stone-600">
                                {{ $booking->adults }}A
                                @if($booking->children > 0) {{ $booking->children }}C @endif
                                @if($booking->pets > 0) 🐾{{ $booking->pets }} @endif
                            </td>
                            <td class="px-4 py-4 font-semibold text-stone-900">₱{{ number_format($booking->total_price, 2) }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'checked_in' => 'bg-emerald-100 text-emerald-700',
                                        'checked_out' => 'bg-stone-100 text-stone-600',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'no_show' => 'bg-stone-200 text-stone-500',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusClasses[$booking->status] ?? 'bg-stone-100 text-stone-600' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $booking->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                       class="px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium rounded-lg transition-colors">
                                        View
                                    </a>
                                    @if($booking->status === 'confirmed')
                                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-medium rounded-lg transition-colors">
                                                Check In
                                            </button>
                                        </form>
                                    @endif
                                    @if($booking->status === 'checked_in')
                                        <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-700 text-xs font-medium rounded-lg transition-colors">
                                                Check Out
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16 text-center text-stone-400">
                                <div class="text-4xl mb-3">📋</div>
                                <p class="font-medium">No bookings found</p>
                                <p class="text-sm mt-1">Try adjusting your filters or create a new booking.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="px-4 py-4 border-t border-stone-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
