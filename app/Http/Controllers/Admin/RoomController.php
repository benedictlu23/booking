<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddonService;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::with(['category', 'bookings'])
            ->orderBy('category_id')
            ->orderBy('room_number')
            ->paginate(20);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $categories = RoomCategory::where('is_active', true)->get();
        $amenities = Amenity::orderBy('name')->get();
        $addons = AddonService::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.rooms.create', compact('categories', 'amenities', 'addons'));
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
            'status' => ['required', 'in:available,occupied,maintenance,housekeeping,blocked,out_of_service'],
            'booking_type' => ['required', 'in:overnight,day_pass,both'],
            'type' => ['nullable', 'in:room,cottage'],
            'is_active' => ['boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:room_amenities,id'],
            'addon_services' => ['nullable', 'array'],
            'addon_services.*.id' => ['required', 'exists:room_addon_services,id'],
            'addon_services.*.max_quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $validated['type'] = $validated['type'] ?? 'room';
        $room = Room::create($validated);

        if ($request->has('amenities')) {
            $room->amenities()->sync($request->amenities);
        }

        $addonSync = [];
        if ($request->has('addon_services')) {
            foreach ($request->input('addon_services') as $item) {
                if (! empty($item['id'])) {
                    $maxQty = ! empty($item['max_quantity']) && (int) $item['max_quantity'] > 0
                        ? (int) $item['max_quantity']
                        : null;
                    $addonSync[(int) $item['id']] = [
                        'max_quantity' => $maxQty,
                    ];
                }
            }
        }
        $room->addonServices()->sync($addonSync);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('rooms/'.$room->id, 'public');
                $room->photos()->create([
                    'path' => $path,
                    'sort_order' => $index,
                    'is_cover' => $index === 0,
                ]);
            }
            $room->update(['gallery' => $room->photos()->pluck('path')->toArray()]);
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} created successfully.");
    }

    public function show(Room $room): View
    {
        $room->load(['category', 'amenities', 'photos', 'addonServices', 'bookings' => fn ($q) => $q->latest()->take(10)]);

        return view('admin.rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        $categories = RoomCategory::where('is_active', true)->get();
        $amenities = Amenity::orderBy('name')->get();
        $addons = AddonService::where('is_active', true)->orderBy('sort_order')->get();
        $room->load(['amenities', 'photos', 'addonServices']);

        return view('admin.rooms.edit', compact('room', 'categories', 'amenities', 'addons'));
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
            'status' => ['required', 'in:available,occupied,maintenance,housekeeping,blocked,out_of_service'],
            'booking_type' => ['required', 'in:overnight,day_pass,both'],
            'type' => ['nullable', 'in:room,cottage'],
            'is_active' => ['boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:room_amenities,id'],
            'addon_services' => ['nullable', 'array'],
            'addon_services.*.id' => ['required', 'exists:room_addon_services,id'],
            'addon_services.*.max_quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $validated['type'] = $validated['type'] ?? $room->type ?? 'room';
        $room->update($validated);
        $room->amenities()->sync($request->amenities ?? []);

        $addonSync = [];
        if ($request->has('addon_services')) {
            foreach ($request->input('addon_services') as $item) {
                if (! empty($item['id'])) {
                    $maxQty = ! empty($item['max_quantity']) && (int) $item['max_quantity'] > 0
                        ? (int) $item['max_quantity']
                        : null;
                    $addonSync[(int) $item['id']] = [
                        'max_quantity' => $maxQty,
                    ];
                }
            }
        }
        $room->addonServices()->sync($addonSync);

        if ($request->hasFile('photos')) {
            $hasCover = $room->photos()->where('is_cover', true)->exists();
            $existingPhotoCount = $room->photos()->count();
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('rooms/'.$room->id, 'public');
                $room->photos()->create([
                    'path' => $path,
                    'sort_order' => $existingPhotoCount + $index,
                    'is_cover' => ! $hasCover && $index === 0,
                ]);
            }
            $room->update(['gallery' => $room->photos()->pluck('path')->toArray()]);
        }

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Room updated successfully.');
    }

    public function quickStatus(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,maintenance,housekeeping,blocked,out_of_service'],
        ]);

        $room->update(['status' => $validated['status']]);

        return back()->with('success', "Room {$room->room_number} status updated to {$validated['status']}.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room {$room->room_number} deleted.");
    }

    public function destroyPhoto(Room $room, RoomPhoto $photo): RedirectResponse
    {
        if ($photo->room_id !== $room->id) {
            abort(404);
        }

        // Remove from storage
        Storage::disk('public')->delete($photo->path);

        $photo->delete();

        // Keep gallery array strictly in sync with actual photos
        $room->update(['gallery' => $room->photos()->pluck('path')->toArray()]);

        return back()->with('success', 'Photo removed successfully.');
    }
}
