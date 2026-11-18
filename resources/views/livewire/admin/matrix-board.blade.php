<?php

use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public string $startDate = '';
    public int $days = 7;
    public ?int $categoryId = null;
    public string $statusFilter = 'active';

    public function mount(?string $initialStart = null, ?int $initialDays = null): void
    {
        $this->startDate = $initialStart ?: request('start', today()->format('Y-m-d'));
        $this->days = $initialDays ?: (int) request('days', 7);
        $this->statusFilter = request('status', 'active');
    }

    public function previousPeriod(): void
    {
        $start = Carbon::parse($this->startDate);
        $this->startDate = $start->subDays($this->days)->format('Y-m-d');
    }

    public function nextPeriod(): void
    {
        $start = Carbon::parse($this->startDate);
        $this->startDate = $start->addDays($this->days)->format('Y-m-d');
    }

    public function today(): void
    {
        $this->startDate = today()->format('Y-m-d');
    }

    public function getDateRangeProperty(): Collection
    {
        $start = Carbon::hasFormat($this->startDate, 'Y-m-d')
            ? Carbon::parse($this->startDate)
            : today();

        return collect(range(0, $this->days - 1))
            ->map(fn ($i) => $start->copy()->addDays($i));
    }

    public function getCategoriesProperty(): EloquentCollection
    {
        return RoomCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getRoomsProperty(): EloquentCollection
    {
        $start = $this->dateRange->first()->copy()->startOfDay();
        $end = $this->dateRange->last()->copy()->endOfDay();

        return Room::with([
            'category',
            'bookings' => function ($q) use ($start, $end) {
                if ($this->statusFilter === 'active') {
                    $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
                } elseif ($this->statusFilter !== 'all') {
                    $q->where('status', $this->statusFilter);
                }

                $q->where(function ($sub) use ($start, $end) {
                    $sub->whereBetween('check_in', [$start, $end])
                        ->orWhereBetween('check_out', [$start, $end])
                        ->orWhere(fn ($nested) => $nested->where('check_in', '<=', $start)->where('check_out', '>=', $end));
                });
            },
        ])
        ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
        ->orderBy('category_id')
        ->orderBy('room_number')
        ->get();
    }
}; ?>

<div>
    {{-- Operational Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 bg-white p-4 rounded-2xl border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)]">
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="previousPeriod"
                    class="px-3.5 py-2 bg-[#FAF8F4] border border-[#233D32]/15 hover:bg-[#EFEBE1] rounded-xl text-xs font-semibold uppercase tracking-wider text-[#233D32] transition-colors shadow-2xs">
                ← Prev {{ $days }}D
            </button>

            <button type="button" wire:click="today"
                    class="px-3.5 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] rounded-xl text-xs font-semibold uppercase tracking-widest transition-colors shadow-2xs">
                Today
            </button>

            <button type="button" wire:click="nextPeriod"
                    class="px-3.5 py-2 bg-[#FAF8F4] border border-[#233D32]/15 hover:bg-[#EFEBE1] rounded-xl text-xs font-semibold uppercase tracking-wider text-[#233D32] transition-colors shadow-2xs">
                Next {{ $days }}D →
            </button>

            <input type="date" wire:model.live="startDate"
                   class="px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-2xs">

            <select wire:model.live="days"
                    class="px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-2xs">
                <option value="7">7 Days Window</option>
                <option value="14">14 Days Window</option>
                <option value="30">30 Days Window</option>
            </select>

            <select wire:model.live="categoryId"
                    class="px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-2xs">
                <option value="">All Accommodation Types</option>
                @foreach($this->categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                    class="px-3 py-2 bg-[#FAF8F4] border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32] shadow-2xs">
                <option value="active">Active (Pending, Confirmed, In-House)</option>
                <option value="all">All (incl. Checked Out & Cancelled)</option>
                <option value="pending">⏳ Pending Only</option>
                <option value="confirmed">✓ Confirmed Only</option>
                <option value="checked_in">👤 Checked In (In-House) Only</option>
                <option value="checked_out">🚪 Checked Out Only</option>
                <option value="cancelled">✕ Cancelled Only</option>
            </select>
        </div>

        <a href="{{ route('admin.bookings.create') }}"
           class="px-4 py-2 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] text-xs font-semibold uppercase tracking-widest rounded-xl transition-all shadow-xs">
            + New Walk-In
        </a>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 p-4 bg-white rounded-2xl border border-[#233D32]/10 shadow-[0_4px_20px_rgba(35,61,50,0.04)]">
        <span class="text-[10px] font-semibold text-[#A68B61] uppercase tracking-[0.2em]">Timeline Legend:</span>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-[#FAF8F4] border border-[#233D32]/20 text-[#233D32] flex items-center justify-center text-[10px] font-bold">+</div><span class="text-xs text-[#595D58]">Available</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-[#233D32] text-[#F7F5EF] flex items-center justify-center text-[9px]">👤</div><span class="text-xs text-[#595D58]">In-House</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-emerald-100 border border-emerald-300 text-emerald-800 flex items-center justify-center text-[9px]">✓</div><span class="text-xs text-[#595D58]">Confirmed</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center text-[9px]">⏳</div><span class="text-xs text-[#595D58]">Pending</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-stone-200 border border-stone-300 text-stone-700 flex items-center justify-center text-[9px]">🚪</div><span class="text-xs text-[#595D58]">Checked Out</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-rose-50 border border-rose-200 text-rose-500 text-[9px] flex items-center justify-center font-bold">✕</div><span class="text-xs text-[#595D58]">Cancelled</span></div>
        <div class="flex items-center gap-1.5"><span class="text-xs text-[#A68B61]">☀️</span><span class="text-xs text-[#595D58]">Day Pass</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded bg-stone-300 text-stone-700 flex items-center justify-center text-[9px]">🔧</div><span class="text-xs text-[#595D58]">Maintenance</span></div>
    </div>

    {{-- Matrix Grid --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(35,61,50,0.04)] border border-[#233D32]/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse min-w-max">
                <thead>
                    <tr class="bg-[#FAF8F4] border-b border-[#233D32]/10">
                        <th class="sticky left-0 z-10 bg-[#FAF8F4] p-3.5 text-left font-serif font-bold text-[#233D32] min-w-44 border-r border-[#233D32]/10">
                            Accommodation Unit
                        </th>
                        @foreach($this->dateRange as $date)
                            <th class="p-2.5 text-center font-medium min-w-[76px] border-r border-[#233D32]/5 {{ $date->isToday() ? 'bg-[#233D32]/10 text-[#233D32] font-bold' : 'text-[#777A72]' }}">
                                <div class="text-[10px] uppercase tracking-wider font-semibold">{{ $date->format('D') }}</div>
                                <div class="font-serif {{ $date->isToday() ? 'font-bold text-[#233D32]' : '' }}">{{ $date->format('M d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $currentCategory = null; @endphp
                    @foreach($this->rooms as $room)
                        @if($room->category_id !== $currentCategory)
                            @php $currentCategory = $room->category_id; @endphp
                            <tr class="bg-[#172A22]">
                                <td colspan="{{ $this->dateRange->count() + 1 }}" class="px-4 py-2.5 text-[#F7F5EF] font-serif font-bold text-xs uppercase tracking-[0.2em]">
                                    {{ $room->category?->name }}
                                </td>
                            </tr>
                        @endif

                        <tr class="border-b border-[#233D32]/5 hover:bg-[#FAF8F4]/50 transition-colors">
                            <td class="sticky left-0 z-10 bg-white p-3 border-r border-[#233D32]/10">
                                <div class="font-serif font-bold text-[#233D32] text-sm">{{ $room->room_number }}</div>
                                <div class="text-[#777A72] text-[11px] flex items-center gap-1.5 mt-0.5">
                                    <span class="truncate max-w-[120px]">{{ $room->name }}</span>
                                    @if($room->booking_type === 'day_pass')
                                        <span class="text-[9px] bg-[#EFEBE1] text-[#A68B61] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wider">Day</span>
                                    @endif
                                </div>
                            </td>

                            @foreach($this->dateRange as $date)
                                @php
                                    $bookingForDate = $room->bookings
                                        ->sortBy(fn ($b) => match ($b->status) {
                                            'checked_in' => 1,
                                            'confirmed' => 2,
                                            'pending' => 3,
                                            'checked_out' => 4,
                                            default => 5,
                                        })
                                        ->first(function ($b) use ($date) {
                                            $bIn = Carbon::parse($b->check_in)->startOfDay();
                                            $bOut = Carbon::parse($b->check_out)->startOfDay();

                                            // Day Pass or same-day booking occupies this single date
                                            if ($bIn->equalTo($bOut) || $b->booking_type === 'day_pass') {
                                                return $date->equalTo($bIn);
                                            }

                                            // Overnight stay: check_in <= date < check_out
                                            return $date->gte($bIn) && $date->lt($bOut);
                                        });

                                    $isMaintenance = in_array($room->status, ['maintenance', 'out_of_service', 'blocked']);
                                    $isHousekeeping = $room->status === 'housekeeping';
                                @endphp

                                <td class="p-1 border-r border-[#233D32]/5 text-center">
                                    @if($isMaintenance)
                                        <div class="h-9 rounded-lg bg-stone-200 text-stone-700 flex items-center justify-center font-semibold text-[10px]" title="{{ ucfirst($room->status) }}">
                                            🔧 Mnt
                                        </div>
                                    @elseif($isHousekeeping && !$bookingForDate)
                                        <div class="h-9 rounded-lg bg-[#EFEBE1] text-[#78866B] border border-[#233D32]/10 flex items-center justify-center font-semibold text-[10px]" title="Housekeeping in Progress">
                                            🧹 Clean
                                        </div>
                                    @elseif($bookingForDate)
                                        @php
                                            $status = $bookingForDate->status;
                                            $isDayPass = $bookingForDate->booking_type === 'day_pass'
                                                || Carbon::parse($bookingForDate->check_in)->isSameDay(Carbon::parse($bookingForDate->check_out));

                                            $cellBg = match($status) {
                                                'checked_in' => 'bg-[#233D32] text-[#F7F5EF] border border-[#172A22] hover:bg-[#172A22]',
                                                'confirmed' => 'bg-emerald-100 text-emerald-950 border border-emerald-300 hover:bg-emerald-200',
                                                'pending' => 'bg-amber-100 text-amber-950 border border-amber-300 hover:bg-amber-200',
                                                'checked_out' => 'bg-stone-200 text-stone-800 border border-stone-300 hover:bg-stone-300',
                                                'cancelled' => 'bg-rose-50 text-rose-500 border border-rose-200 line-through opacity-70 hover:bg-rose-100',
                                                default => 'bg-rose-100 text-rose-800 border border-rose-200 hover:bg-rose-200',
                                            };

                                            $statusIcon = match($status) {
                                                'checked_in' => '👤',
                                                'confirmed' => '✓',
                                                'pending' => '⏳',
                                                'checked_out' => '🚪',
                                                'cancelled' => '✕',
                                                default => '📅',
                                            };
                                        @endphp
                                        <a href="{{ route('admin.bookings.show', $bookingForDate) }}"
                                           class="h-9 rounded-lg {{ $cellBg }} flex flex-col items-center justify-center font-medium text-[10px] p-0.5 transition-all group block relative shadow-2xs"
                                           title="Guest: {{ $bookingForDate->guest_name }} | Status: {{ ucfirst(str_replace('_', ' ', $status)) }} | Type: {{ $isDayPass ? 'Day Pass' : 'Overnight' }} | Code: {{ $bookingForDate->booking_code }} | Payment: {{ ucfirst($bookingForDate->payment_status) }}">
                                            <div class="flex items-center gap-0.5 truncate max-w-[65px] font-bold leading-tight">
                                                <span>{{ $statusIcon }}</span>
                                                <span class="truncate">{{ $bookingForDate->guest_name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-[8px] opacity-85 leading-none mt-0.5 font-mono">
                                                @if($isDayPass)
                                                    <span title="Day Pass">☀️</span>
                                                @endif
                                                @if($bookingForDate->is_walk_in)
                                                    <span title="Walk-In">🚶</span>
                                                @endif
                                                <span>{{ substr($bookingForDate->booking_code, -4) }}</span>
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.bookings.create', ['room_id' => $room->id, 'check_in' => $date->format('Y-m-d')]) }}"
                                           class="h-9 rounded-lg bg-[#FAF8F4] hover:bg-[#EFEBE1] text-[#78866B] hover:text-[#233D32] border border-transparent hover:border-[#233D32]/10 flex items-center justify-center text-[11px] font-bold transition-all"
                                           title="Book Room {{ $room->room_number }} on {{ $date->format('M d') }}">
                                            +
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
</div>