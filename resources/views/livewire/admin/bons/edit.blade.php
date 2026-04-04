<?php

use Livewire\Volt\Component;
use App\Models\Bon;
use App\Models\User;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public Bon $bon;

    public $user_id;
    public $status;
    public $payment_status;
    public $payment_method;
    public $delivery_type;
    public $pickup_date;
    public $price;
    public $notes;
    public $code;

    public function mount(Bon $bon)
    {
        $this->bon = $bon;
        $this->user_id = $bon->user_id;
        $this->status = $bon->status;
        $this->payment_status = $bon->payment_status;
        $this->payment_method = $bon->payment_method;
        $this->delivery_type = $bon->delivery_type;
        // Since sqlite/mysql might return standard datetime, format for 'date' input
        $this->pickup_date = $bon->pickup_date ? \Carbon\Carbon::parse($bon->pickup_date)->format('Y-m-d') : null;
        $this->price = $bon->price;
        $this->notes = $bon->notes;
        $this->code = $bon->code;
    }

    public function generateQrCode()
    {
        if (!$this->code) {
            $this->addError('code', 'Code is empty.');
            return '';
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($this->code);
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|unique:bons,code,' . $this->bon->id,
            'price' => 'required|numeric|min:0',
            'pickup_date' => 'nullable|date',
        ]);

        $this->bon->update([
            'code' => $this->code,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'delivery_type' => $this->delivery_type,
            'pickup_date' => $this->pickup_date,
            'price' => $this->price,
            'notes' => $this->notes,
        ]);

        session()->flash('status', 'Bon updated successfully.');

        return redirect()->route('admin.bons.index');
    }

    public function with(): array
    {
        return [
            'users' => User::where('role', 'client')->get(),
        ];
    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        {{ __('Edit Bon') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <a href="{{ route('admin.bons.index') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Bons') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('Edit') }} #{{ $bon->code }}</span>
        </span>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <form wire:submit="save" class="space-y-6">
            @if (session('success'))
                <div class="p-4 bg-success/10 border border-success/20 rounded-2xl text-success flex items-center shadow-lg shadow-success/5 animate-in fade-in slide-in-from-top-4">
                    <i data-lucide="check-circle" class="w-6 h-6 me-4"></i>
                    <span class="text-base font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Bon Details -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="package" class="w-5 h-5 me-2 text-primary"></i>
                            Bon Details
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Code -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6"> 
                                <div class="space-y-2 col-span-3">
                                    <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Bon Code</label>
                                    <input wire:model="code" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" >
                                    <x-input-error :messages="$errors->get('code')" />
                                </div>
                                <div class="space-y-1 col-span-1 mt-8">
                                    <button type="button" wire:click="generateQrCode" class="block w-full px-4 py-3 bg-primary border border-primary rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium text-white">Generate</button>
                                </div>
                            </div>

                            <!-- Client -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Client</label>
                                <select wire:model="user_id" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                    <option value="">{{ __('Select client') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('user_id')" />
                            </div>

                            <!-- Price -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Total Price (DH)</label>
                                <div class="relative">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">DH</span>
                                    <input wire:model="price" type="number" step="0.01" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium pr-12">
                                </div>
                                <x-input-error :messages="$errors->get('price')" />
                            </div>

                            <!-- Pickup Date -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Pickup Date</label>
                                <input wire:model="pickup_date" type="date" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                <x-input-error :messages="$errors->get('pickup_date')" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="settings" class="w-5 h-5 me-2 text-primary"></i>
                            Settings
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Status -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Status</label>
                                <select wire:model="status" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="processing">{{ __('Processing') }}</option>
                                    <option value="completed">{{ __('Completed') }}</option>
                                    <option value="cancelled">{{ __('Cancelled') }}</option>
                                </select>
                            </div>

                            <!-- Payment Status -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Payment Status</label>
                                <select wire:model="payment_status" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                    <option value="unpaid">{{ __('Unpaid') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Payment Method</label>
                                <select wire:model="payment_method" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="transfer">{{ __('Bank Transfer') }}</option>
                                </select>
                            </div>

                            <!-- Delivery Type -->
                            <div class="space-y-1">
                                <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Type</label>
                                <select wire:model="delivery_type" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                                    <option value="standard">{{ __('Standard') }}</option>
                                    <option value="express">{{ __('Express') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-1">
                            <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Notes</label>
                            <textarea wire:model="notes" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium h-24"></textarea>
                        </div>
                    </div>
                </div>

                <!-- QR Code Preview -->
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <i data-lucide="qr-code" class="w-5 h-5 me-2 text-primary"></i>
                            QR Code Preview
                        </h3>
                        
                        @php
                            $qrUrl = $this->generateQrCode();
                        @endphp

                        @if($qrUrl)
                            <img src="{{ $qrUrl }}" alt="QR Code for {{ $this->code }}" class="w-48 h-48 rounded-2xl shadow-lg border border-gray-100">
                            <p class="text-sm text-gray-400 mt-4 text-center">Scan to view bon details</p>
                        @else
                            <div class="w-48 h-48 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                                <i data-lucide="qr-code" class="w-16 h-16"></i>
                            </div>
                            <p class="text-sm text-gray-400 mt-4 text-center">QR code will appear here</p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('admin.bons.index') }}" class="px-6 py-3 text-sm bg-gray-50 text-gray-700 rounded-xl font-bold hover:bg-gray-100 transition-all">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="px-6 py-3 text-sm bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
                                <i data-lucide="save" class="w-4 h-4 me-2"></i>
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
