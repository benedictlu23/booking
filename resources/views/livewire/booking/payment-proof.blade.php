<?php

use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    public function getPaymentMethodsProperty(): EloquentCollection
    {
        return PaymentMethod::enabled()->get();
    }

    public function getSelectedMethodProperty(): ?PaymentMethod
    {
        return PaymentMethod::where('code', $this->method)->first();
    }

    public function submitProof(): void
    {
        $this->validate([
            'method' => ['required', 'string', 'exists:room_payment_methods,code'],
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
        <div class="p-5 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/15">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-[#EFEBE1] border border-[#A68B61]/30 flex items-center justify-center shrink-0 text-[#233D32] text-xl">
                    🧾
                </div>
                <div>
                    <p class="font-serif font-bold text-[#233D32] text-sm">Payment Proof Submitted</p>
                    <p class="text-xs text-[#595D58] mt-0.5 font-light">
                        Method: <span class="capitalize font-semibold text-[#292D29]">{{ $booking->payment_method }}</span> · Our front-desk team is verifying your payment slip.
                    </p>
                    @if($successMessage)
                        <p class="text-xs text-[#233D32] font-semibold mt-1">✓ {{ $successMessage }}</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="p-6 bg-[#FAF8F4] rounded-2xl border border-[#233D32]/15 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase tracking-[0.16em] font-semibold text-[#A68B61]">Digital Settlement</span>
                    <h3 class="font-serif font-bold text-[#233D32] text-sm">Submit Payment Receipt (Optional)</h3>
                </div>
                <span class="text-[11px] text-[#777A72] font-light">or settle upon arrival</span>
            </div>
            <p class="text-xs text-[#595D58] font-light leading-relaxed">
                If paying via digital wallet or bank deposit, review the official accounts below and upload your receipt screenshot for front-desk clearance.
            </p>

            {{-- Accounts & Scannable QR Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                @foreach($this->paymentMethods->where('code', '!=', 'cash') as $pm)
                    <div class="p-3.5 bg-white rounded-xl border border-[#233D32]/10 shadow-2xs space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-[#233D32]">{{ $pm->name }}</p>
                                @if($pm->account_number)
                                    <p class="font-mono text-[11px] font-bold text-[#292D29] mt-0.5">{{ $pm->account_number }}</p>
                                @endif
                                @if($pm->account_name)
                                    <p class="text-[10px] text-[#777A72]">{{ $pm->account_name }}</p>
                                @endif
                            </div>
                            @if($pm->qr_code_path)
                                <a href="{{ $pm->qr_code_url ?? (str_starts_with($pm->qr_code_path, 'http') ? $pm->qr_code_path : asset('storage/' . $pm->qr_code_path)) }}" download="{{ $pm->code }}-qr-code" title="Download QR"
                                   class="w-10 h-10 rounded-lg overflow-hidden border border-stone-200 bg-white p-0.5 shrink-0 hover:border-[#233D32] transition-colors block">
                                    <img src="{{ $pm->qr_code_url ?? (str_starts_with($pm->qr_code_path, 'http') ? $pm->qr_code_path : asset('storage/' . $pm->qr_code_path)) }}" alt="{{ $pm->name }} QR" class="w-full h-full object-contain">
                                </a>
                            @endif
                        </div>
                        @if($pm->qr_code_path)
                            <div class="pt-1 border-t border-stone-100 flex items-center justify-between">
                                <span class="text-[10px] text-[#777A72]">QR Code Available</span>
                                <a href="{{ $pm->qr_code_url ?? (str_starts_with($pm->qr_code_path, 'http') ? $pm->qr_code_path : asset('storage/' . $pm->qr_code_path)) }}" download="{{ $pm->code }}-qr-code"
                                   class="text-[10px] font-semibold text-[#233D32] hover:text-[#A68B61] flex items-center gap-1">
                                    <span>⬇</span> Download QR
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <form wire:submit="submitProof" action="{{ route('booking.payment-proof', $booking) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                @csrf
                <div class="space-y-1">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72]">Payment Method</label>
                    <select wire:model.live="method" name="payment_method" class="w-full px-3.5 py-2.5 bg-white border border-[#233D32]/15 rounded-xl text-xs font-medium text-[#292D29] focus:outline-none focus:ring-2 focus:ring-[#233D32]/20 focus:border-[#233D32]">
                        @foreach($this->paymentMethods as $pm)
                            <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($this->selectedMethod && $this->selectedMethod->code !== 'cash')
                    @if($this->selectedMethod->qr_code_path)
                        <div class="p-3 bg-white border border-[#233D32]/10 rounded-xl flex items-center gap-4">
                            <img src="{{ $this->selectedMethod->qr_code_url ?? (str_starts_with($this->selectedMethod->qr_code_path, 'http') ? $this->selectedMethod->qr_code_path : asset('storage/' . $this->selectedMethod->qr_code_path)) }}" alt="{{ $this->selectedMethod->name }} QR" class="w-20 h-20 object-contain p-1 border border-stone-200 rounded-lg">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-[#233D32]">Scan or Save {{ $this->selectedMethod->name }} QR</p>
                                <a href="{{ $this->selectedMethod->qr_code_url ?? (str_starts_with($this->selectedMethod->qr_code_path, 'http') ? $this->selectedMethod->qr_code_path : asset('storage/' . $this->selectedMethod->qr_code_path)) }}" download="{{ $this->selectedMethod->code }}-qr-code"
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-[#A68B61] hover:underline">
                                    <span>⬇</span> Download QR Image
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#777A72]">Payment Slip Image</label>
                        <input type="file" wire:model="paymentProof" name="payment_proof" accept="image/*"
                               class="w-full text-xs text-[#595D58] file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#233D32] file:text-[#F7F5EF] hover:file:bg-[#172A22] file:cursor-pointer">
                        @error('paymentProof') <span class="text-xs text-rose-600 block mt-1">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="paymentProof" class="text-xs text-[#A68B61] mt-1 font-medium animate-pulse">Uploading preview...</div>
                    </div>

                    @if ($paymentProof)
                        <div class="mt-2 p-2 bg-white rounded-xl border border-[#233D32]/10 inline-block">
                            <p class="text-[11px] text-[#777A72] mb-1">Receipt Preview:</p>
                            <img src="{{ $paymentProof->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-lg border border-[#233D32]/10">
                        </div>
                    @endif
                @endif

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-3 bg-[#233D32] hover:bg-[#172A22] text-[#F7F5EF] font-semibold uppercase tracking-wider rounded-xl text-xs transition-all shadow-xs disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitProof">Submit Verification Slip</span>
                    <span wire:loading wire:target="submitProof">Submitting Slip...</span>
                </button>
            </form>
        </div>
    @endif
</div>
