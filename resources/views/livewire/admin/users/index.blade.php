<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $role = '';
    public string $status = '';

    public function with()
    {
        return [
            'users' => User::query()
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('role', 'like', '%' . $this->search . '%');
                })
                ->when($this->role !== '', function ($query) {
                    $query->where('role', $this->role);
                })
                ->when($this->status !== '', function ($query) {
                    $query->where('is_active', $this->status);
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        
        // Don't allow deleting yourself
        if ($user->id === Auth::user()->id) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        if ($user->role === 'admin') {
            session()->flash('error', 'You cannot delete an admin.');
            return;
        }


        $user->delete();

        session()->flash('status', 'User deleted successfully.');
    }
}; ?>

<div class="space-y-6">
    <x-slot name="header">
        User Management
    </x-slot>

    <x-slot name="breadcrump">
        <span class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors text-gray-400">Dashboard</a>
            <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-300"></i>
            <span class="text-text-main font-medium">Users</span>
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.users.create') }}" class="px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center">
            <i data-lucide="user-plus" class="w-4 h-4 me-2"></i>
            Add New User
        </a>
    </x-slot>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex flex-col md:flex-row items-center gap-4 flex-1">
                <div class="relative w-full md:w-96">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="text-gray-400 w-5 h-5"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium" placeholder="Search users by name, email or role...">
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="relative flex-1 md:w-40">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="shield" class="text-gray-400 w-4 h-4"></i>
                        </span>
                        <select wire:model.live="role" class="block w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium appearance-none">
                            <option value="">{{ __('All Roles') }}</option>
                            <option value="admin">{{ __('Admin') }}</option>
                            <option value="manager">{{ __('Manager') }}</option>
                            <option value="driver">{{ __('Driver') }}</option>
                            <option value="client">{{ __('Client') }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="text-gray-400 w-4 h-4"></i>
                        </div>
                    </div>

                    <div class="relative flex-1 md:w-40">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="activity" class="text-gray-400 w-4 h-4"></i>
                        </span>
                        <select wire:model.live="status" class="block w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-sm font-medium appearance-none">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i data-lucide="chevron-down" class="text-gray-400 w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2 text-sm text-gray-500 font-medium">
                <i data-lucide="users" class="w-5 h-5 text-primary"></i>
                <span>Total: {{ \App\Models\User::count() }} Users</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">User</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Role</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Contact</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Joined</th>
                        <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/30 transition-colors group" wire:key="{{ $user->id }}">
                            <td class="px-8 py-5">
                                <div class="flex items-center space-x-4">
                                    <x-user-avatar :user="$user" class="w-12 h-12 rounded-2xl overflow-hidden bg-gray-100 border border-gray-100 shadow-sm relative group-hover:scale-105 transition-transform" />
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">ID: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $typeStyles = [
                                        'admin' => 'bg-red-100 text-red-600 border-red-200',
                                        'manager' => 'bg-amber-100 text-amber-600 border-amber-200',
                                        'driver' => 'bg-indigo-100 text-indigo-600 border-indigo-200',
                                        'client' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                    ];
                                    $style = $typeStyles[$user->role] ?? 'bg-gray-100 text-gray-500 border-gray-200';

                                @endphp
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border {{ $style }}">
                                        {{ $user->role }}
                                    </span>

                                    @if(!$user->is_active)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-400 border border-gray-200 rounded-full text-[8px] font-black uppercase tracking-tighter">
                                            Inactive
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-success/5 text-success border border-success/10 rounded-full text-[8px] font-black uppercase tracking-tighter">
                                            Active
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="space-y-1">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 me-2 text-gray-400"></i>
                                        {{ $user->email }}
                                    </div>
                                    @if($user->driver && $user->driver->phone)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i data-lucide="phone" class="w-3.5 h-3.5 me-2 text-gray-400"></i>
                                            {{ $user->driver->phone }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm text-gray-600 font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase">{{ $user->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="p-2.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all" title="Edit User">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </a>
                                    @if($user->role !== 'admin')

                                    <button 
                                        wire:click="deleteUser({{ $user->id }})" 
                                        wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" 
                                        title="Delete User"
                                    >
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">No users found</h3>
                                    <p class="text-gray-400 mt-1">Try adjusting your search or filters to find what you're looking for.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-8 right-8 p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl flex items-center z-50 animate-in slide-in-from-bottom-8">
            <i data-lucide="check-circle" class="w-6 h-6 me-3"></i>
            <span class="font-bold">{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-8 right-8 p-4 bg-red-600 text-white rounded-2xl shadow-2xl flex items-center z-50 animate-in slide-in-from-bottom-8">
            <i data-lucide="alert-circle" class="w-6 h-6 me-3"></i>
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif
</div>