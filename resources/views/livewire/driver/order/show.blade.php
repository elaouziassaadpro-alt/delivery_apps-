<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;


new #[Layout('layouts.driver')] class extends Component
{
    public Order $order;
    

    public function mount(Order $order)
    {
        $this->order = $order;
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
                        <a href="{{ route('driver.bons.show', $order->bon) }}" class="flex items-center space-x-2 text-primary hover:text-primary/80 transition-colors">
                            <span class="text-2xl font-black">#{{ $order->bon->code }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <!-- Recipient -->
                    <div class="space-y-1">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Recipient</label>
                        <div class="text-lg font-bold text-gray-900">{{ $order->recipient->name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->recipient->phone }}</div>
                    </div>

                    <!-- Address -->
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs uppercase tracking-widest font-bold text-gray-400">Delivery Address</label>
                        <div class="text-lg font-bold text-gray-900">{{ $order->address }}</div>
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