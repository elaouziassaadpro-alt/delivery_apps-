<?php

use App\Models\Order;
use App\Models\Recipient;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Bon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Added for API calls
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    use WithFileUploads;

    // Recipient fields
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $email = '';

    // Order fields
    public $client_id;
    public $driver_id;
    public $vehicle_id;
    public string $code = '';
    public $qr_file;
    public string $location = '';
    public $price = 0;
    public $bon_id;
    
    // Coordinates
    public $lat, $lng;

    public function with()
    {
        return [
            'clients' => Client::with('user')->get(),
            'drivers' => Driver::with('user')->get(),
            'vehicles' => Vehicle::all(),
            'bons' => Bon::all(),
        ];
    }
    public function generateQrCode()
    {
        if (!$this->code) {
            $this->addError('code', 'Code is empty.');
            return;
        }

        $qrData = 'Bon: ' . $this->code;
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrData);

        return $qrUrl;
    }
    /**
     * This function runs every time the 'location' property is updated.
     * We use Nominatim (OpenStreetMap) to get Lat/Lng for free.
     */
    public function updatedLocation()
    {
        // 1. Reset if empty
        if (empty($this->location)) {
            $this->lat = null;
            $this->lng = null;
            return session()->message('Code is required.');
        }

        // 2. Clear old coordinates while searching
        $this->lat = null;
        $this->lng = null;

        try {
            $response = Http::withHeaders(['User-Agent' => 'LaravelDeliveryApp'])
                ->timeout(5) // Don't let the app hang if API is slow
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $this->location . ', Morocco', 
                    'format' => 'json',
                    'limit' => 1
                ]);

            if ($response->successful() && isset($response->json()[0])) {
                $this->lat = $response->json()[0]['lat'];
                $this->lng = $response->json()[0]['lon'];
            } else {
                // Optional: Add a validation error if location isn't found
                $this->addError('location', 'Could not find GPS coordinates for this address.');
            }
        } catch (\Exception $e) {
            logger('Geocoding error: ' . $e->getMessage());
        }
    }

    public function createDelivery()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:255',
            'client_id' => 'required|exists:clients,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'code' => 'nullable|string|max:255|unique:orders,code',
            'qr_file' => 'nullable|image|max:1024',
            'location' => 'required|string',
            'price' => 'required|numeric|min:0',
            'bon_id' => 'nullable|exists:bons,id',
        ]);

        DB::transaction(function () {
            // 1. Create Recipient
            $recipient = Recipient::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'email' => $this->email,
            ]);

            // 2. Prepare Order Data
            $client = Client::find($this->client_id);
            $driver = $this->driver_id ? Driver::find($this->driver_id) : null;
            $vehicle = $this->vehicle_id ? Vehicle::find($this->vehicle_id) : null;

            $finalCode = $this->code ?: 'DEL-' . strtoupper(bin2hex(random_bytes(3)));
            $qrPath = $this->qr_file ? $this->qr_file->store('orders/qr', 'public') : null;

            Order::create([
                'client_id' => $this->client_id,
                'driver_id' => $this->driver_id,
                'recipient_id' => $recipient->id,
                'vehicle_id' => $this->vehicle_id,
                'code' => $finalCode,
                'qr_file' => $qrPath,
                'location' => $this->location,
                'lat' => $this->lat, // Now saving Lat
                'lng' => $this->lng, // Now saving Lng
                'price' => $this->price,
                'driver_commission' => $driver ? $driver->commission : 0,
                'commission' => $client ? $client->commission : 0,
                'vehicle_license_plate' => $vehicle ? $vehicle->license_plate : null,
                'status' => 'pending',
                'bon_id' => $this->bon_id,
            ]);
        });

        session()->flash('status', 'Delivery created successfully.');
        return $this->redirect(route('admin.deliveries.index'), navigate: true);
    }
}; ?>

<div class="space-y-8 pb-20">
    <x-slot name="header">
        Create New Delivery
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center text-sm">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-bold">New Delivery</span>
        </span>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <form wire:submit="createDelivery" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Recipient Information -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                            <i data-lucide="user"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Recipient Details</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">First Name</label>
                            <input wire:model="first_name" type="text" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                            <x-input-error :messages="$errors->get('first_name')" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Last Name</label>
                            <input wire:model="last_name" type="text" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                            <x-input-error :messages="$errors->get('last_name')" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Phone Number</label>
                        <input wire:model="phone" type="text" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Email (Optional)</label>
                        <input wire:model="email" type="email" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                </div>

                <!-- Assignment & Details -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center text-secondary">
                            <i data-lucide="truck"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Assignment</h2>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Assign Client</label>
                        <select wire:model="client_id" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="">Select a Client</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->user->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Assign Driver (Optional)</label>
                        <select wire:model="driver_id" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="">No Driver Assigned</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('driver_id')" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Select Vehicle (Optional)</label>
                        <select wire:model="vehicle_id" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="">No Vehicle Assigned</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->make }} ({{ $v->license_plate }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('vehicle_id')" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Select bon (Optional)</label>
                        <select wire:model="bon_id" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="">No bon Assigned</option>
                            @foreach($bons as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->license_plate }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('vehicle_id')" />
                    </div>


                    <div class="pt-4 space-y-4">
                        <!-- Code -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6"> 
                                <div class="space-y-2 col-span-3">
                                    <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Code</label>
                                    <input wire:model="code" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" >
                                    <x-input-error :messages="$errors->get('code')" />
                                </div>
                                <div class="space-y-1 col-span-1 mt-8">
                                    <button type="button" wire:click="generateQrCode" class="block w-full px-4 py-3 bg-primary border border-primary rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium text-white">Generate</button>
                                </div>
                            </div>
                        <div class="space-y-1">
                            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center">
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
                                    <div class="w-48 h-48 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-300">
                                        <i data-lucide="qr-code" class="w-16 h-16"></i>
                                    </div>
                                    <p class="text-sm text-gray-400 mt-4 text-center">QR code will appear here</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & Price -->
                <div class="lg:col-span-2 bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-8">
                    <div class="flex items-center justify-between border-b border-gray-50 pb-6">
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Delivery Specifics</h2>
                        <i data-lucide="map-pin" class="w-8 h-8 text-primary/20"></i>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-sm font-black text-gray-700 uppercase tracking-widest">
                                    Delivery Address (Location)
                                </label>
                                
                                <div wire:loading wire:target="location" class="flex items-center text-primary text-[10px] font-bold uppercase animate-pulse">
                                    <svg class="animate-spin h-3 w-3 mr-1" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Searching...
                                </div>

                                @if($lat && $lng)
                                    <div wire:loading.remove wire:target="location" class="flex items-center text-green-600 text-[10px] font-bold uppercase">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        GPS Verified
                                    </div>
                                @endif
                            </div>

                            <textarea 
                                wire:model.blur="location" 
                                class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-semibold h-32" 
                                placeholder="Type address (e.g. Maarif, Casablanca) and click outside to verify...">
                            </textarea>
                            
                            <x-input-error :messages="$errors->get('location')" />

                            <input type="hidden" wire:model="lat">
                            <input type="hidden" wire:model="lng">
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-sm font-black text-gray-700 uppercase tracking-widest">Total Price ($)</label>
                                <div class="relative">
                                    <i data-lucide="dollar-sign" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-300"></i>
                                    <input wire:model="price" type="number" step="0.01" class="block w-full pl-12 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-lg font-black text-primary">
                                </div>
                                <x-input-error :messages="$errors->get('price')" />
                            </div>

                            <button type="submit" class="w-full py-6 bg-primary text-white rounded-[2rem] font-black text-lg shadow-2xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 transition-all flex items-center justify-center">
                                <i data-lucide="package-plus" class="w-6 h-6 me-3"></i>
                                Create Delivery
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('livewire:navigated', () => {
        lucide.createIcons();
    });
</script>
