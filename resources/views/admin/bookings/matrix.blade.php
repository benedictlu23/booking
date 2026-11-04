<x-layouts.admin>
    <x-slot:heading>Reservation Matrix</x-slot:heading>
    <x-slot:subheading>2D Resource Timeline — Real-time room availability grid</x-slot:subheading>

    <livewire:admin.matrix-board :initialStart="$startDate->format('Y-m-d')" :initialDays="$days" />
</x-layouts.admin>
