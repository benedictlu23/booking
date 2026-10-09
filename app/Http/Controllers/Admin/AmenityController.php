<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(): View
    {
        $amenities = Amenity::withCount('rooms')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.amenities.index', compact('amenities'));
    }

    public function create(): View
    {
        return view('admin.amenities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:room_amenities,name'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        Amenity::create($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity '{$validated['name']}' created successfully.");
    }

    public function edit(Amenity $amenity): View
    {
        return view('admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:room_amenities,name,'.$amenity->id],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $amenity->update($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity '{$amenity->name}' updated successfully.");
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        $amenity->rooms()->detach();
        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity '{$amenity->name}' deleted.");
    }
}
