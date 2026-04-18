<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;


new #[Layout('layouts.driver')] class extends Component
{
    public Order $order;
    public $goToLocation;
    

    public function mount(Order $order)
    {
        $this->order = $order;
    }
    public function goToLocationGoogleMaps()
    {
        $getlocationmaps = $this->order->location;
        $this->goToLocation = "https://www.google.com/maps/search/?api=1&query=" . urlencode($getlocationmaps);
        return $this->goToLocation;
    }
    public function goToLocationWaze()
    {
        $location = $this->order->location;

        // Using rawurlencode for safer URL parsing
        return "https://waze.com/ul?q=" . rawurlencode($location) . "&navigate=yes";
    }   
}
?>

<div class="p-4 md:p-6 lg:p-8 space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 transition-all duration-500">
        <div class="space-y-1">
            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-widest mb-2">
                <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                <span>Order Details</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">Order <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">#{{ $order->code }}</span></h1>
            <p class="text-gray-500 font-medium text-lg">Track and manage this delivery.</p>
        </div>
        
        <div class="flex items-center gap-4 w-full md:w-auto">
            <a href="{{ route('driver.bons.index') }}" class="flex items-center space-x-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-[1.5rem] font-bold text-sm hover:bg-gray-200 transition-all duration-300">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Deliveries</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Card -->
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i data-lucide="package" class="w-5 h-5 me-2 text-primary"></i>
                        Order Information
                    </h3>
                    <span class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider 
                        @if(strtolower($order->status) === 'pending') bg-yellow-100 text-yellow-700
                        @elseif(strtolower($order->status) === 'assigned') bg-blue-100 text-blue-700
                        @elseif(strtolower($order->status) === 'delivered') bg-green-100 text-green-700
                        @elseif(strtolower($order->status) === 'returned') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Code -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Order Code</label>
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-black text-gray-900">#{{ $order->code }}</span>
                            <button wire:click="copyCode" class="text-gray-400 hover:text-primary transition-colors">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Bon -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Bon</label>
                        <a href="{{ route('driver.bons.show', $order->bonDriver->code) }}" class="flex items-center space-x-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="text-2xl font-black">#{{ $order->bonDriver->code }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                    @if ($order->vehicle)
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Vehicle</label>
                        <div class="text-lg font-bold text-gray-900">{{ $order->vehicle->make }} {{ $order->vehicle->model }}</div>
                        <div class="text-sm text-gray-500">{{ $order->vehicle->license_plate }}</div>
                    </div>
                    @endif
                    <!-- Recipient -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Recipient</label>
                        <div class="text-lg font-bold text-gray-900">{{ $order->recipient->first_name }} {{ $order->recipient->last_name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->recipient->phone }}</div>
                    </div>
                    

                    <!-- Address -->
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Address</label>
                        <div class="text-lg font-bold text-gray-900 hover:text-primary"><a href="{{ $this->goToLocationGoogleMaps() }}"target="_blank">{{ $order->location }}</a></div>
                        @if($order->notes)
                            <div class="mt-2 p-3 bg-gray-50 rounded-xl text-sm text-gray-600 border border-gray-100">
                                <span class="font-bold text-gray-500">Notes:</span> {{ $order->notes }}
                            </div>
                        @endif
                    </div>

                    <!-- Price -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Price</label>
                        <div class="text-2xl font-black text-gray-900">{{ $order->price }} DH</div>
                    </div>

                    <!-- Payment Status -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Payment Status</label>
                        <div class="text-2xl font-black text-gray-900">
                            @if(strtolower($order->payment_status) === 'paid')
                                <span class="text-green-600">Paid</span>
                            @else
                                <span class="text-red-600">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Delivery Type -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Type</label>
                        <div class="text-lg font-bold text-gray-900">{{ ucfirst($order->delivery_type) }}</div>
                    </div>

                    <!-- Pickup Date -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Pickup Date</label>
                        <div class="text-lg font-bold text-gray-900">{{ $order->pickup_date ? \Carbon\Carbon::parse($order->pickup_date)->format('M d, Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    <div>
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4 mb-4">

        <label class="text-xs uppercase tracking-widest font-bold text-gray-400 ">
            Delivery Address
        </label>

        <div class="text-lg font-bold text-blue-900">
            {{ $order->location }}
        </div>

        <a href="{{ $this->goToLocationWaze() }}" target="_blank"
   class="inline-flex items-center gap-2 px-6 py-3 rounded-full 
          bg-[#00B9FF] text-white font-semibold text-sm
          shadow-lg hover:bg-gradient-to-r hover:from-[#00B9FF] hover:to-[#009FD4]
          hover:scale-105 transition-all duration-300 ease-in-out
          transform">
                <!-- Waze Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path d="M20.54,6.63C21.23,7.57 21.69,8.67 21.89,9.82C22.1,11.07 22,12.34 21.58,13.54C21.18,14.71 20.5,15.76 19.58,16.6C18.91,17.24 18.15,17.77 17.32,18.18C17.73,19.25 17.19,20.45 16.12,20.86C15.88,20.95 15.63,21 15.38,21C14.27,21 13.35,20.11 13.31,19C13.05,19 10.73,19 10.24,19C10.13,20.14 9.11,21 7.97,20.87C6.91,20.77 6.11,19.89 6.09,18.83C6.1,18.64 6.13,18.44 6.19,18.26C4.6,17.73 3.21,16.74 2.19,15.41C1.86,14.97 1.96,14.34 2.42,14C2.6,13.86 2.82,13.78 3.05,13.78C3.77,13.78 4.05,13.53 4.22,13.15C4.46,12.43 4.6,11.68 4.61,10.92C4.64,10.39 4.7,9.87 4.78,9.35C5.13,7.62 6.1,6.07 7.5,5C9.16,3.7 11.19,3 13.29,3C14.72,3 16.13,3.35 17.4,4C18.64,4.62 19.71,5.5 20.54,6.63M16.72,17.31C18.5,16.5 19.9,15.04 20.59,13.21C22.21,8.27 18,4.05 13.29,4.05C12.94,4.05 12.58,4.07 12.23,4.12C9.36,4.5 6.4,6.5 5.81,9.5C5.43,11.5 6,14.79 3.05,14.79C4,16 5.32,16.93 6.81,17.37C7.66,16.61 8.97,16.69 9.74,17.55C9.85,17.67 9.94,17.8 10,17.94C10.59,17.94 13.2,17.94 13.55,17.94C14.07,16.92 15.33,16.5 16.35,17.04C16.5,17.12 16.6,17.21 16.72,17.31Z"/>
                </svg>

                Open in Waze
            </a>

        </div>

        <!-- Right Column: Quick Actions & Status -->
        <div class="space-y-6">
            <!-- Scan / Verification Info -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i data-lucide="map-pin" class="w-5 h-5 me-2 text-primary"></i>
                    Delivery Updates
                </h3>
                
                <p class="text-gray-500 text-sm">Update the delivery status once you arrive at the recipient's location.</p>
                
                @if(strtolower($order->status) !== 'delivered' && strtolower($order->status) !== 'returned')
                <div class="space-y-3 pt-2">
                    <button class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 transition-colors shadow-lg shadow-green-500/20 active:scale-[0.98]">
                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                        <span>Confirm Delivery</span>
                    </button>
                    
                    <button class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-white text-red-500 border-2 border-red-100 rounded-xl font-bold hover:bg-red-50 transition-colors active:scale-[0.98]">
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                        <span>Mark as Returned</span>
                    </button>
                </div>
                @else
                <div class="flex items-center justify-center p-4 bg-gray-50 rounded-xl outline outline-1 outline-gray-200">
                    <div class="flex items-center space-x-2 text-gray-500 font-bold text-sm">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>This order is completed and locked.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    </div>
</div>