<?php

use App\Models\Bon;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
new #[Layout('layouts.driver')] class extends Component
{
    public $bon = true;
    public $orders_page = false;
    public $code;
    public $user_id;
    public $status;
    public $payment_status;
    public $payment_method;
    public $delivery_type;
    public $pickup_date;
    public $price;
    public $driver_commission;
    public $commission;
    public $weight;
    public $dimensions_length;
    public $dimensions_width;
    public $dimensions_height;
    public $notes;
    public function generateQrCode()
    {
        if (!$this->code) {
            $this->addError('code', 'Code is empty.');
            return;
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($this->code);

        return $qrUrl;
    }
    public function createBon()
    {
        $this->validate([
            'code' => 'required|string|max:255|unique:bons,code',
        ]);

        $bon = Bon::create([
            'code' => $this->code,
            'user_id' => Auth::user()->id,
            'status' => $this->status ?? 'pending',
            'payment_status' => $this->payment_status ?? 'unpaid',
            'payment_method' => $this->payment_method ?? 'cash',
            'delivery_type' => $this->delivery_type ?? 'standard',
            'pickup_date' => $this->pickup_date,
            'price' => $this->price ?? 0,
            'driver_commission' => $this->driver_commission ?? 0,
            'commission' => $this->commission ?? 0,
            'weight' => $this->weight,
            'dimensions_length' => $this->dimensions_length,
            'dimensions_width' => $this->dimensions_width,
            'dimensions_height' => $this->dimensions_height,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Bon delivery run created successfully! You can now scan orders to assign them to this Bon.');
        return redirect()->route('driver.bons.show', $bon->id);
    }
    
}
?>
<div class="space-y-6">
    <x-slot name="header">
        {{ __('Create Bon') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <a href="{{ route('admin.bons.index') }}" class="hover:text-primary transition-colors">{{ __('Bons') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('Create') }}</span>
        </span>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <form wire:submit="createBon" class="space-y-6">
            @if (session('success'))
                <div class="p-4 bg-success/10 border border-success/20 rounded-2xl text-success flex items-center shadow-lg shadow-success/5 animate-in fade-in slide-in-from-top-4">
                    <i data-lucide="check-circle" class="w-6 h-6 me-4"></i>
                    <span class="text-base font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Bon Details -->
                <div class="space-y-6">
                    <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 space-y-4 md:space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="package" class="w-5 h-5 me-2 text-primary"></i>
                            Bon Details
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Code -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4 md:gap-6"> 
                                <div class="space-y-2 sm:col-span-2 md:col-span-3">
                                    <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Bon Code</label>
                                    <input wire:model="code" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium" >
                                    <x-input-error :messages="$errors->get('code')" />
                                </div>
                                <div class="space-y-1 sm:col-span-1 sm:mt-8 mt-1">
                                    <button type="button" wire:click="generateQrCode" class="block w-full px-4 py-3 bg-primary border border-primary rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium text-white shadow-md shadow-primary/20 hover:scale-[1.02] active:scale-95">Generate</button>
                                </div>
                            </div>
                            <!-- Price -->
                            <div class="space-y-1">
                                <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Total Price (DH)</label>
                                <div class="relative">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">DH</span>
                                    <input wire:model="price" type="number" step="0.01" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium pr-12">
                                </div>
                                <x-input-error :messages="$errors->get('price')" />
                            </div>

                            <!-- Pickup Date -->
                            <div class="space-y-1">
                                <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Pickup Date</label>
                                <input wire:model="pickup_date" type="date" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                                <x-input-error :messages="$errors->get('pickup_date')" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 space-y-4 md:space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="settings" class="w-5 h-5 me-2 text-primary"></i>
                            Settings
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Status -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Status</label>
                                <select wire:model="status" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="processing">{{ __('Processing') }}</option>
                                    <option value="completed">{{ __('Completed') }}</option>
                                    <option value="cancelled">{{ __('Cancelled') }}</option>
                                </select>
                            </div>

                            <!-- Payment Status -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Payment Status</label>
                                <select wire:model="payment_status" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                                    <option value="unpaid">{{ __('Unpaid') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Payment Method</label>
                                <select wire:model="payment_method" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="transfer">{{ __('Bank Transfer') }}</option>
                                </select>
                            </div>

                            <!-- Delivery Type -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Type</label>
                                <select wire:model="delivery_type" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                                    <option value="standard">{{ __('Standard') }}</option>
                                    <option value="express">{{ __('Express') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1">
                            <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Notes</label>
                            <textarea wire:model="notes" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium h-24"></textarea>
                        </div>
                    </div>
                </div>

                <!-- QR Code Preview -->
                <div class="space-y-6">
                    <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <i data-lucide="qr-code" class="w-5 h-5 me-2 text-primary"></i>
                            QR Code Preview
                        </h3>
                        
                        @php
                            $qrUrl = $this->generateQrCode($this->code);
                        @endphp
                        @if($qrUrl)
                            <img src="{{ $qrUrl }}" alt="QR Code for {{ $this->code }}" class="w-48 h-48 rounded-2xl shadow-lg border border-gray-100">
                            <p class="text-sm text-gray-400 mt-4 text-center">Scan to view bon details</p>
                        @else
                            <div class="w-32 h-32 md:w-48 md:h-48 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                                <i data-lucide="qr-code" class="w-12 h-12 md:w-16 md:h-16"></i>
                            </div>
                            <p class="text-sm text-gray-400 mt-4 text-center">QR code will appear here</p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100">
                        <div class="flex flex-col-reverse md:flex-row justify-end gap-3">
                            <a href="{{ route('driver.bons.index') }}" class="w-full md:w-auto px-6 py-3.5 text-center text-sm bg-gray-50 text-gray-700 rounded-xl font-bold hover:bg-gray-100 transition-all">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="w-full md:w-auto px-6 py-3.5 text-sm bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                                <i data-lucide="plus-circle" class="w-4 h-4 me-2"></i>
                                {{ __('Create Bon') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>