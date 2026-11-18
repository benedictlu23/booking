<x-layouts.app>
    <x-slot:title>Guest Portal | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-20 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Profile Folio Header --}}
            <div class="bg-[#172A22] text-[#F7F5EF] rounded-3xl p-6 sm:p-10 mb-8 relative overflow-hidden border border-[#233D32]/20 shadow-[0_12px_40px_rgba(23,42,34,0.18)]">
                <div class="absolute -right-12 -bottom-12 w-80 h-80 bg-white/[0.02] rounded-full pointer-events-none"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                 class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-[#A68B61]/40 shadow-md">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-[#233D32] rounded-2xl flex items-center justify-center text-[#F7F5EF] font-serif font-bold text-2xl sm:text-3xl border border-[#A68B61]/30 shadow-md">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.22em] text-[#A68B61] font-semibold">Resort Guest & Member</span>
                            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#F7F5EF] mt-0.5">{{ $user->name }}</h1>
                            <p class="text-xs text-[#A8B29A] mt-1 font-light">{{ $user->email }}</p>
                            @if($user->last_visited_at)
                                <p class="text-[11px] text-[#A8B29A]/80 mt-1">Last stay {{ $user->last_visited_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center sm:self-center gap-4">
                        <div class="bg-[#233D32]/70 backdrop-blur-xs rounded-2xl px-6 py-4 border border-white/10 text-center min-w-[130px]">
                            <p class="text-2xl sm:text-3xl font-serif font-bold text-[#A68B61]">{{ $user->visit_count }}</p>
                            <p class="text-[10px] uppercase tracking-[0.16em] text-[#A8B29A] mt-0.5">Resort Visits</p>
                        </div>
                        <a href="{{ route('rooms.search') }}"
                           class="hidden sm:inline-flex items-center justify-center px-5 py-3.5 bg-[#A68B61] hover:bg-[#8e7550] text-[#172A22] font-semibold text-xs uppercase tracking-widest rounded-xl transition-all shadow-sm">
                            Book A Stay
                        </a>
                    </div>
                </div>
            </div>

            {{-- Active Bookings --}}
            <div class="mb-10">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">Your Itinerary</span>
                        <h2 class="text-xl sm:text-2xl font-serif font-bold text-[#233D32]">Active Reservations</h2>
                    </div>
                    <a href="{{ route('customer.bookings') }}" class="text-xs uppercase tracking-wider font-semibold text-[#233D32] hover:text-[#A68B61] transition-colors flex items-center gap-1">
                        <span>View all bookings</span>
                        <span>→</span>
                    </a>
                </div>

                @forelse($activeBookings as $booking)
                    <a href="{{ route('customer.bookings.show', $booking) }}"
                       class="group block bg-white rounded-2xl p-5 sm:p-6 shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 hover:border-[#233D32]/30 hover:shadow-[0_8px_30px_rgba(35,61,50,0.08)] transition-all mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#FAF8F4] border border-[#233D32]/10 shrink-0 flex items-center justify-center">
                                    @if($booking->room?->photos->first())
                                        <img src="{{ $booking->room->photos->first()->path }}" alt="{{ $booking->room->name }}" class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ asset('storage/assets/AIR-LOGO.png') }}" alt="Resort" class="w-8 h-8 object-contain opacity-70">
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-semibold text-[#A68B61] tracking-wider">{{ $booking->booking_code }}</span>
                                        <span class="text-xs text-[#777A72]">· Room {{ $booking->room?->room_number }}</span>
                                    </div>
                                    <p class="font-serif font-bold text-base sm:text-lg text-[#233D32] group-hover:text-[#A68B61] transition-colors mt-0.5">
                                        {{ $booking->room?->name }}
                                    </p>
                                    <p class="text-xs text-[#777A72] mt-0.5 font-light">
                                        {{ $booking->check_in->format('M d') }} → {{ $booking->check_out->format('M d, Y') }}
                                        ({{ $booking->nights }} night{{ $booking->nights !== 1 ? 's' : '' }})
                                    </p>
                                </div>
                            </div>
                            <div class="flex sm:flex-col items-center sm:items-end justify-between pt-3 sm:pt-0 border-t sm:border-t-0 border-[#233D32]/5">
                                <p class="font-serif font-bold text-lg text-[#233D32]">₱{{ number_format($booking->total_price, 2) }}</p>
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'confirmed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'checked_in' => 'bg-[#233D32]/10 text-[#233D32] border-[#233D32]/20',
                                    ];
                                    $badgeClass = $statusBadges[$booking->status] ?? 'bg-stone-100 text-stone-700 border-stone-200';
                                @endphp
                                <span class="inline-block px-3 py-1 text-[11px] uppercase tracking-wider font-semibold rounded-lg border {{ $badgeClass }} mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="bg-white rounded-3xl p-10 sm:p-14 text-center border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)]">
                        <div class="w-12 h-12 rounded-2xl bg-[#FAF8F4] border border-[#233D32]/10 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-[#78866B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-serif font-bold text-lg text-[#233D32]">No Active Reservations</h3>
                        <p class="text-xs text-[#777A72] mt-1 mb-6 font-light">Escape the noise. Find your quiet cottage surrounded by nature.</p>
                        <a href="{{ route('rooms.search') }}"
                           class="inline-flex items-center px-6 py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs uppercase tracking-widest font-semibold rounded-xl transition-all shadow-sm">
                            Browse Accommodations
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Past Bookings --}}
            @if($pastBookings->isNotEmpty())
                <div>
                    <div class="mb-4">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#A68B61]">History</span>
                        <h2 class="text-xl font-serif font-bold text-[#233D32]">Past Stays & Completed Trips</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($pastBookings as $booking)
                            <a href="{{ route('customer.bookings.show', $booking) }}"
                               class="group bg-white rounded-2xl p-5 border border-[#233D32]/10 hover:border-[#233D32]/30 hover:shadow-sm transition-all block">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-serif font-semibold text-stone-900 group-hover:text-[#A68B61] transition-colors">{{ $booking->room?->name }}</p>
                                        <p class="text-xs text-[#777A72] mt-0.5">{{ $booking->check_in->format('M d') }} – {{ $booking->check_out->format('M d, Y') }}</p>
                                    </div>
                                    <span class="text-xs font-mono font-medium text-[#777A72] uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>