<?php

namespace App\Http\Controllers;

use App\Models\AddonService;
use App\Models\Carousel;
use App\Models\ResortSetting;
use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RoomSearchController extends Controller
{
    public function index(): View
    {
        $carousels = Carousel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $categories = RoomCategory::where('is_active', true)
            ->with(['rooms' => fn ($q) => $q->where('is_active', true)->where('status', 'available')])
            ->get();

        $featuredRooms = Room::with(['category', 'photos'])
            ->where('is_active', true)
            ->where('status', 'available')
            ->take(6)
            ->get();

        $resortLat = ResortSetting::get('latitude', '14.5995');
        $resortLng = ResortSetting::get('longitude', '120.9842');
        $resortAddress = ResortSetting::get('resort_address', 'Agro-Inland Resort, Philippines');
        $contactDetails = ResortSetting::get('contact_details', '');
        $petPolicy = ResortSetting::get('pet_policy', 'Pets are welcome with an additional fee per night.');

        return view('welcome', compact(
            'carousels', 'categories', 'featuredRooms',
            'resortLat', 'resortLng', 'resortAddress', 'contactDetails', 'petPolicy'
        ));
    }

    public function search(Request $request): View
    {
        $checkIn = $request->get('check_in', today()->format('Y-m-d'));
        $checkOut = $request->get('check_out', today()->addDay()->format('Y-m-d'));
        $adults = (int) $request->get('adults', 1);
        $children = (int) $request->get('children', 0);
        $pets = (int) $request->get('pets', 0);
        $categoryId = $request->get('category_id');
        $maxPrice = $request->get('max_price');
        $minPrice = $request->get('min_price');
        $bookingType = $request->get('booking_type', 'overnight');

        $rooms = Room::with(['category', 'photos', 'amenities'])
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('capacity_adults', '>=', $adults)
            ->when($pets > 0, fn ($q) => $q->where('allows_pets', true))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($maxPrice, fn ($q) => $q->where('base_price', '<=', $maxPrice))
            ->when($minPrice, fn ($q) => $q->where('base_price', '>=', $minPrice))
            ->when($bookingType === 'day_pass', fn ($q) => $q->whereIn('booking_type', ['day_pass', 'both']))
            ->when($bookingType === 'overnight', fn ($q) => $q->whereIn('booking_type', ['overnight', 'both']))
            ->get()
            ->filter(fn (Room $room) => $room->isAvailableFor(
                Carbon::parse($checkIn),
                Carbon::parse($checkOut)
            ));

        $categories = RoomCategory::where('is_active', true)->get();
        $petPolicy = ResortSetting::get('pet_policy', '');

        return view('rooms.search', compact(
            'rooms', 'categories', 'checkIn', 'checkOut',
            'adults', 'children', 'pets', 'bookingType', 'petPolicy'
        ));
    }

    public function show(Room $room): View
    {
        $room->load(['category', 'amenities', 'photos']);

        $addons = AddonService::where('is_active', true)->orderBy('sort_order')->get();

        $petPolicy = ResortSetting::get('pet_policy', 'Pets are welcome with a nightly fee.');

        return view('rooms.show', compact('room', 'addons', 'petPolicy'));
    }
}
