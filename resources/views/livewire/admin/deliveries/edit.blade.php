<?php

use App\Models\Order;
use App\Models\Recipient;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    use WithFileUploads;

    public Order $order;

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
    public $existing_qr; // To show current QR
    public string $location = '';
    public $price = 0;
    public string $status = '';
    
    // Coordinates
    public $lat, $lng;

    public function mount(Order $order)
    {
        $this->order = $order->load('recipient');
        
        // Load Recipient Data
        $this->first_name = $order->recipient->first_name;
        $this->last_name = $order->recipient->last_name;
        $this->phone = $order->recipient->phone;
        $this->email = $order->recipient->email ?? '';

        // Load Order Data
        $this->client_id = $order->client_id;
        $this->driver_id = $order->driver_id;
        $this->vehicle_id = $order->vehicle_id;
        $this->code = $order->code;
        $this->existing_qr = $order->qr_file;
        $this->location = $order->location;
        $this->price = $order->price;
        $this->status = $order->status;
        $this->lat = $order->lat;
        $this->lng = $order->lng;
    }

    public function with()
    {
        return [
            'clients' => Client::with('user')->get(),
            'drivers' => Driver::with('user')->get(),
            'vehicles' => Vehicle::all(),
        ];
    }

    public function updatedLocation()
    {
        if (empty($this->location)) {
            $this->lat = null; $this->lng = null;
            return;
        }

        try {
            $response = Http::withHeaders(['User-Agent' => 'LaravelDeliveryApp'])
                ->timeout(5)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $this->location . ', Morocco', 
                    'format' => 'json',
                    'limit' => 1
                ]);

            if ($response->successful() && isset($response->json()[0])) {
                $this->lat = $response->json()[0]['lat'];
                $this->lng = $response->json()[0]['lon'];
            } else {
                $this->addError('location', 'Coordinates not found for this address.');
            }
        } catch (\Exception $e) {
            logger('Geocoding error: ' . $e->getMessage());
        }
    }

    public function updateDelivery()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:255',
            'client_id' => 'required|exists:clients,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'code' => 'required|string|max:255|unique:orders,code,' . $this->order->id,
            'qr_file' => 'nullable|image|max:1024',
            'location' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        DB::transaction(function () {
            // 1. Update Recipient
            $this->order->recipient->update([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'email' => $this->email,
            ]);

            // 2. Handle QR Code Update
            $qrPath = $this->order->qr_file;
            if ($this->qr_file) {
                if ($this->order->qr_file) {
                    Storage::disk('public')->delete($this->order->qr_file);
                }
                $qrPath = $this->qr_file->store('orders/qr', 'public');
            }

            // 3. Update Order
            $client = Client::find($this->client_id);
            $driver = $this->driver_id ? Driver::find($this->driver_id) : null;
            $vehicle = $this->vehicle_id ? Vehicle::find($this->vehicle_id) : null;

            $this->order->update([
                'client_id' => $this->client_id,
                'driver_id' => $this->driver_id,
                'vehicle_id' => $this->vehicle_id,
                'code' => $this->code,
                'qr_file' => $qrPath,
                'location' => $this->location,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'price' => $this->price,
                'status' => $this->status,
                'driver_commission' => $driver ? $driver->commission : 0,
                'commission' => $client ? $client->commission : 0,
                'vehicle_license_plate' => $vehicle ? $vehicle->license_plate : null,
            ]);
        });

        session()->flash('status', 'Delivery updated successfully.');
        return $this->redirect(route('admin.deliveries.index'), navigate: true);
    }
}; ?>

<div class="space-y-8 pb-20">
    <x-slot name="header">Edit Delivery: {{ $code }}</x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center text-sm">
            <a href="{{ route('admin.deliveries.index') }}" class="hover:text-primary transition-colors text-gray-400">Deliveries</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-bold">Edit Order</span>
        </span>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <form wire:submit="updateDelivery" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
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
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Phone</label>
                        <input wire:model="phone" type="text" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium">
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center text-secondary">
                            <i data-lucide="settings"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Order Management</h2>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Status</label>
                        <select wire:model="status" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="pending">Pending</option>
                            <option value="in transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Assign Driver</label>
                        <select wire:model="driver_id" class="block w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all font-bold text-sm">
                            <option value="">No Driver Assigned</option>
                            @foreach($drivers as $d)
                                <option value="{{ $d->id }}">{{ $d->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase tracking-widest font-black text-gray-400">Update QR</label>
                            <input wire:model="qr_file" type="file" class="block w-full text-xs text-gray-500">
                        </div>
                        <div class="flex items-center justify-center bg-gray-50 rounded-2xl border border-gray-100 p-2">
                             @if($existing_qr)
                                <img src="{{ asset('storage/'.$existing_qr) }}" class="h-12 w-12 object-contain">
                             @else
                                <span class="text-[8px] text-gray-400">No QR</span>
                             @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-sm font-black text-gray-700 uppercase tracking-widest">Address</label>
                                @if($lat && $lng)
                                    <span class="text-green-600 text-[10px] font-bold uppercase">GPS Validated</span>
                                @endif
                            </div>
                            <textarea wire:model.blur="location" class="block w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl h-24 text-sm font-semibold"></textarea>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-1">
                                <label class="text-sm font-black text-gray-700 uppercase tracking-widest">Price ($)</label>
                                <input wire:model="price" type="number" step="0.01" class="block w-full py-4 bg-gray-50 border border-gray-100 rounded-2xl text-center text-lg font-black text-primary">
                            </div>
                            <button type="submit" class="w-full py-4 bg-primary text-white rounded-2xl font-black shadow-lg hover:-translate-y-1 transition-all">
                                Update Delivery
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