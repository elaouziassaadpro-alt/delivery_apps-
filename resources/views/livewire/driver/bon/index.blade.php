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
    public $search = '';
    public $statusFilter = 'all';
    public $paymentFilter = 'all';
    public $sortBy = 'created_at';

    public function with()
    {
        $query = Bon::where('user_id', Auth::user()->id);

        // Status Stats
        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        // Search
        if ($this->search) {
            $query->where('code', 'like', '%' . $this->search . '%');
        }

        // Status Filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Payment Filter
        if ($this->paymentFilter !== 'all') {
            $query->where('payment_status', $this->paymentFilter);
        }

        // Sorting
        $query->orderBy($this->sortBy, 'desc');

        return [
            'bons' => $query->paginate(10),
            'stats' => $stats
        ];
    }

    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
    }
}
?>
<div>
    <x-slot name="header">
        {{ __('Bons') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('driver.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('Bons') }}</span>
        </span>
    </x-slot>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer gap-3 md:gap-0" wire:click="filterByStatus('all')">
                <div>
                    <p class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Total Bons</p>
                    <p class="text-3xl md:text-3xl font-black text-gray-900 mt-1 md:mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-primary/10 p-3 md:p-4 rounded-2xl self-start md:self-auto text-primary">
                    <i data-lucide="package" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
            </div>

            <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer gap-3 md:gap-0" wire:click="filterByStatus('pending')">
                <div>
                    <p class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Pending</p>
                    <p class="text-3xl md:text-3xl font-black text-gray-900 mt-1 md:mt-2">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-yellow-500/10 p-3 md:p-4 rounded-2xl self-start md:self-auto text-yellow-500">
                    <i data-lucide="clock" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
            </div>

            <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer gap-3 md:gap-0" wire:click="filterByStatus('processing')">
                <div>
                    <p class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Processing</p>
                    <p class="text-3xl md:text-3xl font-black text-gray-900 mt-1 md:mt-2">{{ $stats['processing'] }}</p>
                </div>
                <div class="bg-blue-500/10 p-3 md:p-4 rounded-2xl self-start md:self-auto text-blue-500">
                    <i data-lucide="refresh-cw" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
            </div>

            <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between hover:shadow-lg hover:-translate-y-1 transition-all cursor-pointer gap-3 md:gap-0" wire:click="filterByStatus('completed')">
                <div>
                    <p class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Completed</p>
                    <p class="text-3xl md:text-3xl font-black text-gray-900 mt-1 md:mt-2">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-green-500/10 p-3 md:p-4 rounded-2xl self-start md:self-auto text-green-500">
                    <i data-lucide="check-circle" class="w-6 h-6 md:w-8 md:h-8"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-5 md:p-8 rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <i data-lucide="filter" class="w-5 h-5 me-2 text-primary"></i>
                Filter Options
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Search</label>
                    <input wire:model.live="search" type="text" placeholder="Search by code..." class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Status</label>
                    <select wire:model.live="statusFilter" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Payment</label>
                    <select wire:model.live="paymentFilter" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] md:text-xs uppercase tracking-widest font-bold text-gray-400">Sort By</label>
                    <select wire:model.live="sortBy" class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-primary/10 transition-all text-base md:text-sm font-medium">
                        <option value="created_at">Date Created</option>
                        <option value="code">Code</option>
                        <option value="price">Price</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bons Table -->
        <div class="bg-white rounded-3xl md:rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 md:p-8 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i data-lucide="list" class="w-5 h-5 me-2 text-primary"></i>
                    Delivery Runs
                </h3>
            </div>
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto no-scrollbar">
                <table class="w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Bon Code</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Price</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Payment</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pickup Date</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Created At</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50/60" x-init="$nextTick(() => lucide.createIcons())">
                        @forelse($bons as $bon)
                            <tr class="hover:bg-primary/[0.02] transition-colors group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-12 w-12 flex-shrink-0 bg-gray-50 group-hover:bg-primary/10 rounded-2xl flex items-center justify-center transition-colors">
                                            <i data-lucide="package" class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-base font-black text-gray-900">#{{ $bon->code }}</div>
                                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $bon->orders_driver->count() }} Orders</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-base font-black text-gray-900">{{ number_format($bon->price, 2) }} <span class="text-xs text-gray-400 font-bold">DH</span></div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="px-4 py-1.5 inline-flex text-[10px] font-black rounded-full uppercase tracking-widest
                                        @if($bon->status === 'completed') bg-green-100/50 text-green-700 border border-green-100
                                        @elseif($bon->status === 'pending') bg-yellow-100/50 text-yellow-700 border border-yellow-100
                                        @elseif($bon->status === 'cancelled') bg-red-100/50 text-red-700 border border-red-100
                                        @else bg-blue-100/50 text-blue-700 border border-blue-100
                                        @endif">
                                        {{ $bon->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span class="px-4 py-1.5 inline-flex text-[10px] font-black rounded-full border uppercase tracking-widest
                                        {{ $bon->payment_status === 'paid' ? 'border-green-200 text-green-600 bg-green-50/50' : 'border-orange-200 text-orange-600 bg-orange-50/50' }}">
                                        {{ $bon->payment_status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-600">{{ \Carbon\Carbon::parse($bon->pickup_date)->format('M d, Y') }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm text-gray-400 font-semibold">{{ $bon->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ route('driver.bons.show', $bon) }}" class="w-10 h-10 bg-gray-50 hover:bg-primary/10 text-gray-400 hover:text-primary rounded-xl flex items-center justify-center transition-all hover:scale-105" title="Manage assigned orders">
                                            <i data-lucide="scan-line" class="w-5 h-5"></i>
                                        </a>
                                        <a href="{{ route('driver.bons.edit', $bon) }}" class="w-10 h-10 bg-gray-50 hover:bg-primary/10 text-gray-400 hover:text-primary rounded-xl flex items-center justify-center transition-all hover:scale-105">
                                            <i data-lucide="edit-2" class="w-5 h-5"></i>
                                        </a>
                                        <button wire:click="deleteBon({{ $bon->id }})" wire:confirm="Are you sure you want to delete this Bon?" class="w-10 h-10 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition-all hover:scale-105">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                                            <i data-lucide="package-x" class="w-10 h-10 text-gray-300"></i>
                                        </div>
                                        <h3 class="text-lg font-black text-gray-900 mb-2 uppercase tracking-tight">No bons found</h3>
                                        <p class="text-gray-400 font-medium max-w-sm">There are no delivery runs matching the current active criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Stacked Card View -->
            <div class="md:hidden divide-y divide-gray-100/50">
                @forelse($bons as $bon)
                    <div class="p-5 flex flex-col gap-4 hover:bg-gray-50/50 transition-colors">
                        <!-- Header -->
                        <div class="flex flex-row justify-between items-start gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-12 w-12 flex-shrink-0 bg-primary/10 rounded-2xl flex items-center justify-center text-primary">
                                    <i data-lucide="package" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-gray-900">#{{ $bon->code }}</h4>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-0.5">{{ $bon->orders->count() }} Orders</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="px-3 py-1 inline-flex text-[10px] font-black rounded-full uppercase tracking-widest
                                    @if($bon->status === 'completed') bg-green-100/50 text-green-700 border border-green-100
                                    @elseif($bon->status === 'pending') bg-yellow-100/50 text-yellow-700 border border-yellow-100
                                    @elseif($bon->status === 'cancelled') bg-red-100/50 text-red-700 border border-red-100
                                    @else bg-blue-100/50 text-blue-700 border border-blue-100
                                    @endif">
                                    {{ $bon->status }}
                                </span>
                                <span class="px-3 py-1 inline-flex text-[10px] font-black rounded-full border uppercase tracking-widest
                                    {{ $bon->payment_status === 'paid' ? 'border-green-200 text-green-600 bg-green-50/50' : 'border-orange-200 text-orange-600 bg-orange-50/50' }}">
                                    {{ $bon->payment_status }}
                                </span>
                            </div>
                        </div>

                        <!-- Data Grid -->
                        <div class="grid grid-cols-2 gap-4 py-3 border-y border-dashed border-gray-100">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Price</p>
                                <p class="text-base font-black text-gray-900 mt-1">{{ number_format($bon->price, 2) }} <span class="text-[10px] text-gray-400">DH</span></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pickup Date</p>
                                <p class="text-sm font-bold text-gray-600 mt-1">{{ \Carbon\Carbon::parse($bon->pickup_date)->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Full-Width Mobile Actions -->
                        <div class="grid grid-cols-3 gap-3">
                            <a href="{{ route('driver.bons.show', $bon) }}" class="flex flex-col items-center justify-center py-3 bg-primary/5 text-primary rounded-2xl hover:bg-primary/10 transition-colors active:scale-95">
                                <i data-lucide="scan-line" class="w-5 h-5 mb-1"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Scan</span>
                            </a>
                            <a href="{{ route('driver.bons.edit', $bon) }}" class="flex flex-col items-center justify-center py-3 bg-gray-50 text-gray-600 rounded-2xl hover:bg-gray-100 transition-colors active:scale-95">
                                <i data-lucide="edit-2" class="w-5 h-5 mb-1"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Edit</span>
                            </a>
                            <button wire:click="deleteBon({{ $bon->id }})" wire:confirm="Are you sure you want to delete this Bon?" class="flex flex-col items-center justify-center py-3 bg-red-50 text-red-600 rounded-2xl hover:bg-red-100 transition-colors active:scale-95">
                                <i data-lucide="trash-2" class="w-5 h-5 mb-1"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="package-x" class="w-8 h-8 text-gray-300"></i>
                        </div>
                        <h3 class="text-base font-black text-gray-900 mb-1 uppercase tracking-tight">No bons</h3>
                        <p class="text-gray-400 text-sm font-medium">Try clearing filters.</p>
                    </div>
                @endforelse
            </div>
            
            @if($bons->hasPages())
                <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/30">
                    {{ $bons->links() }}
                </div>
            @endif
        </div>
    </div>
</div>