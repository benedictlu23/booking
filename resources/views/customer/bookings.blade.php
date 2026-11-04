<x-layouts.app>
    <x-slot:title>My Bookings</x-slot:title>

    <div class="pt-24 pb-16 min-h-screen bg-stone-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <livewire:customer.bookings-list :initialStatus="request('status')" />
        </div>
    </div>
</x-layouts.app>
