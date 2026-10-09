<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResortSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResortSettingController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $settings = ResortSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
        ]);

        foreach ($request->settings as $key => $value) {
            ResortSetting::set($key, $value ?? '');
        }

        return back()->with('success', 'Resort settings updated successfully.');
    }
}
