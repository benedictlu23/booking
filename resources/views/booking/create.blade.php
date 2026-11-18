<x-layouts.app>
    <x-slot:title>Reserve {{ $room->name }} | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-24 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 border-b border-[#233D32]/10 pb-6">
                <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[#777A72] hover:text-[#233D32] transition-colors mb-3">
                    <span>←</span>
                    <span>Back to {{ $room->name }}</span>
                </a>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#233D32]">Complete Your Reservation</h1>
                <p class="text-xs text-[#777A72] mt-1 font-light">Enter your guest details and preferences to secure your stay.</p>
            </div>

            <livewire:booking.create-form
                :room="$room"
                :initialCheckIn="$checkIn"
                :initialCheckOut="$checkOut"
            />
        </div>
    </div>
</x-layouts.app>
