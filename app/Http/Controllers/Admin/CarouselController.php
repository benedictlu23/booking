<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarouselController extends Controller
{
    public function index(): View
    {
        $carousels = Carousel::orderBy('sort_order')->get();

        return view('admin.carousels.index', compact('carousels'));
    }

    public function create(): View
    {
        return view('admin.carousels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $path = $request->file('image')->store('carousels', 'public');
        $validated['image_path'] = $path;
        unset($validated['image']);

        Carousel::create($validated);

        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide added.');
    }

    public function edit(Carousel $carousel): View
    {
        return view('admin.carousels.edit', compact('carousel'));
    }

    public function update(Request $request, Carousel $carousel): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('carousels', 'public');
            $validated['image_path'] = $path;
        }

        unset($validated['image']);
        $carousel->update($validated);

        return redirect()->route('admin.carousels.index')->with('success', 'Carousel slide updated.');
    }

    public function destroy(Carousel $carousel): RedirectResponse
    {
        $carousel->delete();

        return redirect()->route('admin.carousels.index')->with('success', 'Slide deleted.');
    }
}
