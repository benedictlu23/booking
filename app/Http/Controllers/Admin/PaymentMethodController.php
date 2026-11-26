<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:room_payment_methods,code'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['boolean'],
            'qr_code' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled', true);
        $validated['sort_order'] = (int) ($request->input('sort_order') ?? 0);

        if ($request->hasFile('qr_code')) {
            $path = $request->file('qr_code')->store('payment-methods', 'public');
            $validated['qr_code_path'] = asset('storage/'.$path);
        }

        PaymentMethod::create($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$validated['name']}' created successfully.");
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:room_payment_methods,code,'.$paymentMethod->id],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['boolean'],
            'qr_code' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_qr_code' => ['nullable', 'boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['sort_order'] = (int) ($request->input('sort_order') ?? 0);

        if ($request->boolean('remove_qr_code')) {
            $rawPath = str_replace(asset('storage/'), '', $paymentMethod->qr_code_path ?? '');
            if ($rawPath && Storage::disk('public')->exists($rawPath)) {
                Storage::disk('public')->delete($rawPath);
            }
            $validated['qr_code_path'] = null;
        }

        if ($request->hasFile('qr_code')) {
            $rawPath = str_replace(asset('storage/'), '', $paymentMethod->qr_code_path ?? '');
            if ($rawPath && Storage::disk('public')->exists($rawPath)) {
                Storage::disk('public')->delete($rawPath);
            }
            $path = $request->file('qr_code')->store('payment-methods', 'public');
            $validated['qr_code_path'] = asset('storage/'.$path);
        }

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$paymentMethod->name}' updated successfully.");
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->qr_code_path && Storage::disk('public')->exists($paymentMethod->qr_code_path)) {
            Storage::disk('public')->delete($paymentMethod->qr_code_path);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method deleted successfully.');
    }

    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update([
            'is_enabled' => ! $paymentMethod->is_enabled,
        ]);

        $status = $paymentMethod->is_enabled ? 'enabled' : 'disabled';

        return back()->with('success', "Payment method '{$paymentMethod->name}' has been {$status}.");
    }
}
