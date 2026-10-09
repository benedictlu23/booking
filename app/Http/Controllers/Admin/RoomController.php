<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $rooms = Room::with(['category', 'bookings'])
            ->orderBy('category_id')
            ->orderBy('room_number')
            ->paginate(20);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create(): \Illuminate\View\View
    {
        $categories = RoomCategory::where('is_active', true)->get();
        $amenities = Amenity::orderBy('name')->get();

        return view('admin.rooms.create', compact('categories', 'amenities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:room_categories,id'],
            'room_number' => ['required', 'string', 'unique:rooms,room_number'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity_adults' => ['required', 'integer', 'min:1'],
            'capacity_children' => ['nullable', 'integer', 'min:0'],
            'allows_pets' => ['boolean'],
            'pet_fee' => ['nullable', 'numeric', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'floor_number' => ['nullable', 'integer'],
            'status' => ['required', 'in:available,occupied,maintenance,housekeeping,blocked'],
            'booking_type' => ['required', 'in:overnight,day_pass,both'],
            'is_active' => ['boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $room = Room::create($validated);

        if ($request->has('amenities')) {
            $room->amenities()->sync($request->amenities);
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('rooms/'.$room->id, 'public');
                $room->photos()->create([
                    'path' => $path,
                    'sort_order' => $index,
                    'is_cover' => $index === 0,
                ]);
            }
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} created successfully.");
    }

    public function show(Room $room): \Illuminate\View\View
    {
        $room->load(['category', 'amenities', 'photos', 'bookings' => fn ($q) => $q->latest()->take(10)]);

        return view('admin.rooms.show', compact('room'));
    }

    public function edit(Room $room): \Illuminate\View\View
    {
        $categories = RoomCategory::where('is_active', true)->get();
        $amenities = Amenity::orderBy('name')->get();
        $room->load(['amenities', 'photos']);

        return view('admin.rooms.edit', compact('room', 'categories', 'amenities'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:room_categories,id'],
            'room_number' => ['required', 'string', 'unique:rooms,room_number,'.$room->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity_adults' => ['required', 'integer', 'min:1'],
            'capacity_children' => ['nullable', 'integer', 'min:0'],
            'allows_pets' => ['boolean'],
            'pet_fee' => ['nullable', 'numeric', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'floor_number' => ['nullable', 'integer'],
            'status' => ['required', 'in:available,occupied,maintenance,housekeeping,blocked'],
            'booking_type' => ['required', 'in:overnight,day_pass,both'],
            'is_active' => ['boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ]);

        $room->update($validated);
        $room->amenities()->sync($request->amenities ?? []);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('rooms/'.$room->id, 'public');
                $room->photos()->create([
                    'path' => $path,
                    'sort_order' => $room->photos()->count() + $index,
                    'is_cover' => $room->photos()->count() === 0 && $index === 0,
                ]);
            }
        }

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', "Room updated successfully.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} deleted.");
    }
}
