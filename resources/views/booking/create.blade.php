<x-layouts.app>
    <x-slot:title>Book {{ $room->name }}</x-slot:title>

    <div class="pt-24 pb-20 min-h-screen bg-stone-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 pt-4">
                <a href="{{ route('rooms.show', $room) }}" class="text-sm text-stone-500 hover:text-stone-700">← Back to Room Details</a>
                <h1 class="text-2xl font-black text-stone-900 mt-2">Complete Your Booking</h1>
            </div>

            <livewire:booking.create-form
                :room="$room"
                :initialCheckIn="$checkIn"
                :initialCheckOut="$checkOut"
            />
        </div>
    </div>
</x-layouts.app>
