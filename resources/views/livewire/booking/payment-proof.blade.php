<?php

use App\Models\Booking;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Booking $booking;
    public string $method = 'gcash';
    public $paymentProof;
    public ?string $successMessage = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
        $this->method = $booking->payment_method ?: 'gcash';
    }

    public function submitProof(): void
    {
        $this->validate([
            'method' => ['required', 'in:gcash,bank_transfer,cash'],
            'paymentProof' => ['nullable', 'image', 'max:5120'],
        ]);

        $proofPath = $this->booking->payment_proof;

        if ($this->paymentProof) {
            $proofPath = $this->paymentProof->store('proofs', 'public');
        }

        $this->booking->update([
            'payment_method' => $this->method,
            'payment_proof' => $proofPath,
        ]);

        $this->successMessage = 'Payment proof submitted successfully! Staff will verify shortly.';
    }
}; ?>

<div>
    @if($booking->payment_proof || $successMessage)
        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🧾</span>
                <div>
                    <p class="font-bold text-emerald-900 text-sm">Payment Proof Submitted</p>
                    <p class="text-xs text-emerald-700">
                        Method: <span class="capitalize">{{ $booking->payment_method }}</span> · Our staff is reviewing your payment.
                    </p>
                    @if($successMessage)
                        <p class="text-xs text-emerald-600 font-semibold mt-1">✓ {{ $successMessage }}</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="p-5 bg-stone-50 rounded-2xl border border-stone-200 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-stone-900 text-sm">💳 Pay Online / Manual Transfer (Optional)</h3>
                <span class="text-xs text-stone-400">or pay at check-in</span>
            </div>
            <p class="text-xs text-stone-600">Upload your proof of payment (screenshot/receipt) to confirm your booking immediately.</p>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 bg-white rounded-xl border border-stone-200">
                    <p class="font-bold text-stone-900">GCash</p>
                    <p class="text-stone-500">0912-345-6789</p>
                    <p class="text-stone-400">Agro-Inland Resort</p>
                </div>
                <div class="p-2.5 bg-white rounded-xl border border-stone-200">
                    <p class="font-bold text-stone-900">BDO Unibank</p>
                    <p class="text-stone-500">0012-3456-7890</p>
                    <p class="text-stone-400">Agro-Inland Corp.</p>
                </div>
            </div>

            <form wire:submit="submitProof" action="{{ route('booking.payment-proof', $booking) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Payment Method</label>
                    <select wire:model.live="method" name="payment_method" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash on Arrival</option>
                    </select>
                </div>

                @if($method !== 'cash')
                    <div>
                        <label class="block text-xs font-semibold text-stone-600 mb-1">Proof of Payment Image</label>
                        <input type="file" wire:model="paymentProof" name="payment_proof" accept="image/*"
                               class="w-full text-xs text-stone-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
                        @error('paymentProof') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="paymentProof" class="text-xs text-emerald-600 mt-1">Uploading preview... ⏳</div>
                    </div>

                    @if ($paymentProof)
                        <div class="mt-2">
                            <p class="text-xs text-stone-500 mb-1">Receipt Preview:</p>
                            <img src="{{ $paymentProof->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-xl border border-stone-200">
                        </div>
                    @endif
                @endif

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitProof">Submit Payment Proof</span>
                    <span wire:loading wire:target="submitProof">Submitting...</span>
                </button>
            </form>
        </div>
    @endif
</div>
