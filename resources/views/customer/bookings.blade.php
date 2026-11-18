<x-layouts.app>
    <x-slot:title>My Bookings | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-20 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <livewire:customer.bookings-list :initialStatus="request('status')" />
        </div>
    </div>
</x-layouts.app>