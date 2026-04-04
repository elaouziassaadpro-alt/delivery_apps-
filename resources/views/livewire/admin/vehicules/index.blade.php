<?php

use App\Models\Vehicle;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component 
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $filterType = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }

    public function delete($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        
        session()->flash('status', __('Vehicle deleted successfully.'));
    }

    public function getTypeStyles($type): string
    {
        return match($type) {
            'truck'      => 'bg-amber-100 text-amber-600 border-amber-200',
            'van'        => 'bg-blue-100 text-blue-600 border-blue-200',
            'motorcycle' => 'bg-purple-100 text-purple-600 border-purple-200',
            'car'        => 'bg-emerald-100 text-emerald-600 border-emerald-200',
            default      => 'bg-gray-100 text-gray-500 border-gray-200',
        };
    }

    public function with(): array
    {
        return [
            'vehicles' => Vehicle::query()
                ->with('manager.user')
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('make', 'like', '%' . $this->search . '%')
                          ->orWhere('license_plate', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filterType, function($query) {
                    $query->where('type', $this->filterType);
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    {{-- Layout Slots --}}
    <x-slot name="header">
        {{ __('Fleet Inventory') }}
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">{{ __('Dashboard') }}</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">{{ __('Fleet') }}</span>
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.vehicules.create') }}" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
            <i data-lucide="plus-circle" class="w-4 h-4 me-2"></i>
            {{ __('Add Vehicle') }}
        </a>
    </x-slot>

    {{-- Table Card --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="text-gray-400 w-5 h-5"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" placeholder="{{ __('Search plate or make') }}...">
            </div>

            <div class="flex items-center space-x-4">
                {{-- Filter Type Dropdown --}}
                <select wire:model.live="filterType" class="bg-gray-50 border-gray-100 rounded-xl text-xs font-bold text-gray-500 focus:ring-primary/10 transition-all border">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="car">{{ __('Car') }}</option>
                    <option value="truck">{{ __('Truck') }}</option>
                    <option value="van">{{ __('Van') }}</option>
                </select>

                <div class="flex items-center space-x-2 text-sm text-gray-500 font-medium border-l pl-4 border-gray-100">
                    <i data-lucide="truck" class="w-5 h-5 text-primary"></i>
                    <span>{{ __('Total') }}: {{ $vehicles->total() }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Vehicle') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('License') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Manager') }}</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($vehicles as $vehicle)
                        <tr class="hover:bg-gray-50/30 transition-colors group" wire:key="{{ $vehicle->id }}">
                            <td class="px-8 py-5">
                                <div class="flex items-center space-x-4">
                                    {{-- Custom icon or small image if you have one --}}
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                                        <i data-lucide="{{ $vehicle->type === 'motorcycle' ? 'bike' : 'truck' }}" class="w-6 h-6"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $vehicle->make }}</p>
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-tight border mt-1 inline-block {{ $this->getTypeStyles($vehicle->type) }}">
                                            {{ __($vehicle->type) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900 font-mono tracking-wider">{{ $vehicle->license_plate }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5 uppercase">ID: #{{ str_pad($vehicle->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-500 mr-3 overflow-hidden">
                                        @if($vehicle->manager->user->photo)
                                            <img src="{{  route('profile.photo', ['filename' => basename($vehicle->manager->user->photo)]) }}" alt="{{ $vehicle->manager->user->name ?? 'U' }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($vehicle->manager->user->name ?? 'U', 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $vehicle->manager->user->name ?? __('Unassigned') }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase">{{ __('Fleet Manager') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.vehicules.edit', $vehicle) }}" class="p-2.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all" title="{{ __('Edit') }}">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    <button 
                                        wire:click="delete({{ $vehicle->id }})" 
                                        wire:confirm="{{ __('Are you sure you want to delete this vehicle?') }}"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all"
                                    >
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-300">
                                        <i data-lucide="truck" class="w-10 h-10"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ __('No vehicles found') }}</h3>
                                    <p class="text-gray-400 mt-1">{{ __('Try adjusting your search or filters.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

    {{-- Feedback Notifications --}}
    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-8 right-8 p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl flex items-center z-50 animate-in slide-in-from-bottom-8">
            <i data-lucide="check-circle" class="w-6 h-6 me-3"></i>
            <span class="font-bold">{{ session('status') }}</span>
        </div>
    @endif
</div>