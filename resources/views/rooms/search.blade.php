<x-layouts.app>
    <x-slot:title>Accommodations & Rates | Agro-Inland Resort</x-slot:title>

    <div class="pt-28 pb-24 min-h-screen bg-[#F7F5EF]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:rooms.search-catalog
                :initialCheckIn="$checkIn"
                :initialCheckOut="$checkOut"
                :initialAdults="$adults"
                :initialChildren="$children"
                :initialPets="$pets"
                :initialBookingType="$bookingType"
                :initialCategoryId="request('category_id')"
                :initialMinPrice="request('min_price')"
                :initialMaxPrice="request('max_price')"
            />
        </div>
    </div>
</x-layouts.app>
