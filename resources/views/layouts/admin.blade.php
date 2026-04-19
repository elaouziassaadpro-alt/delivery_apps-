<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo.png') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-page-bg text-text-main">
    <div class="flex min-h-screen relative"
        x-data="{ 
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', 
            mobileMenuOpen: false 
        }"
        x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value ? 'true' : 'false'))"
    >
        <!-- Overlay for Mobile -->
        <div 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside 
            x-cloak
            :class="{
                'w-72': !sidebarCollapsed,
                'w-20': sidebarCollapsed,
                'translate-x-0': mobileMenuOpen,
                '-translate-x-full lg:translate-x-0': !mobileMenuOpen
            }"
            class="bg-[#060a13] text-gray-300 flex-shrink-0 flex flex-col fixed h-full z-40 transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] shadow-2xl shadow-black/40 border-r border-white/5"
        >
            <div class="p-6 flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden group/logo cursor-pointer">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center flex-shrink-0 shadow-[0_0_20px_rgba(var(--primary-rgb),0.3)] group-hover/logo:scale-105 group-hover/logo:rotate-3 transition-all duration-300">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" class="w-6 h-6 drop-shadow-md">
                    </div>
                    <span x-show="!sidebarCollapsed" class="text-xl font-black tracking-tight whitespace-nowrap text-white">
                        DELIVERY<span class="text-primary">.</span>
                    </span>
                </div>
                <button @click="mobileMenuOpen = false" class="lg:hidden p-2 text-gray-500 hover:text-white hover:bg-white/10 rounded-xl transition-all">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <nav class="flex-1 px-4 py-2 space-y-1.5 overflow-y-auto no-scrollbar">
                <div x-show="!sidebarCollapsed" class="px-4 pt-4 pb-2 uppercase text-[10px] font-black tracking-[0.2em] text-gray-500/80">Main Menu</div>
                
                <a href="{{ route('admin.dashboard') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200 relative">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.dashboard') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="layout-dashboard" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'text-white' : '' }}">Dashboard</span>
                </a>

                @if(optional(Auth::user())->isAdmin() || optional(Auth::user())->isManager())

                    <div x-show="!sidebarCollapsed" class="px-4 pt-6 pb-2 uppercase text-[10px] font-black tracking-[0.2em] text-gray-500/80">System Admin</div>
                    
                    <a href="{{ route('admin.users.index') }}" 
                    wire:navigate
                    class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                        <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.users.*') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                            <i data-lucide="users" class="w-full h-full"></i>
                        </div>
                        <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.users.*') ? 'text-white' : '' }}">User Accounts</span>
                    </a>
                @endif

                <div x-show="!sidebarCollapsed" class="px-4 pt-6 pb-2 uppercase text-[10px] font-black tracking-[0.2em] text-gray-500/80">Active Flow</div>
                
                <a href="{{ route('admin.bons.client.index') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.bons.client.*') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.bons.client.*') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="list" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.bons.client.*') ? 'text-white' : '' }}">Bons Clients</span>
                </a>
                <a href="{{ route('admin.bons.driver.index') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.bons.driver.*') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.bons.driver.*') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="user-check" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.bons.driver.*') ? 'text-white' : '' }}">Bons Drivers</span>
                </a>

                <a href="{{ route('admin.deliveries.index') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.deliveries.index') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.deliveries.index') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="package" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.deliveries.index') ? 'text-white' : '' }}">Orders</span>
                </a>

                <div x-show="!sidebarCollapsed" class="px-4 pt-6 pb-2 uppercase text-[10px] font-black tracking-[0.2em] text-gray-500/80">Fleet Management</div>
                
                <a href="{{ route('admin.vehicules.index') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.vehicules.index') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.vehicules.index') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="truck" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.vehicules.create') ? 'text-white' : '' }}">Vehicules</span>
                </a>
            </nav>

            <div class="p-4 bg-[#060a13] border-t border-white/5">
                <a href="{{ route('admin.profile') }}" 
                wire:navigate
                class="flex items-center space-x-3 px-4 py-3 rounded-2xl group {{ request()->routeIs('admin.profile') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs('admin.profile') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}">
                        <i data-lucide="user-circle" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap {{ request()->routeIs('admin.profile') ? 'text-white' : '' }}">My Profile</span>
                </a>


                <button 
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden lg:flex mt-1 w-full items-center space-x-3 px-4 py-3 rounded-2xl text-gray-500 hover:bg-white/5 hover:text-white transition-all duration-200"
                >
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''">
                        <i data-lucide="chevron-left" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-[10px] uppercase tracking-widest">Minimize</span>
                </button>

                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="mt-1 w-full flex items-center space-x-3 px-4 py-3 rounded-2xl text-red-400/70 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200 group">
                    <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center group-hover:-translate-x-1 transition-transform">
                        <i data-lucide="log-out" class="w-full h-full"></i>
                    </div>
                    <span x-show="!sidebarCollapsed" class="font-bold text-sm whitespace-nowrap">Logout</span>
                </button>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div 
            :class="{
                'lg:ml-72': !sidebarCollapsed,
                'lg:ml-20': sidebarCollapsed,
                'ml-0': true 
            }"
            class="flex-1 flex flex-col min-h-screen transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] lg:ml-72"
        >
            <!-- Header -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 lg:px-10 sticky top-0 z-30 shadow-sm shadow-gray-100/20">
                <div class="flex items-center flex-1 space-x-4">
                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = true" class="lg:hidden p-2.5 bg-gray-50 text-gray-500 rounded-xl hover:bg-gray-100 transition-all">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <div class="relative w-full max-w-[30rem] group hidden sm:block">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-300 group-focus-within:text-primary transition-colors">
                            <i data-lucide="search" class="w-5 h-5"></i>
                        </span>
                        <input type="text" class="block w-full pl-12 pr-6 py-2.5 bg-gray-50 border border-transparent rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-sm font-medium placeholder-gray-400/60" placeholder="Find orders or vehicles...">
                    </div>
                </div>

                <div class="flex items-center space-x-4 lg:space-x-8">
                    <div class="hidden lg:flex items-center space-x-4 pr-6 border-r border-gray-100 h-10">
                        <button class="relative p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full outline outline-2 outline-white"></span>
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-3 lg:space-x-4 group cursor-pointer">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-text-main group-hover:text-primary transition-colors truncate w-24 lg:w-32">{{ Auth::user()?->name ?? 'Guest' }}</p>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-0.5">{{ Auth::user()?->role ?? 'User' }}</p>

                        </div>
                        <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl lg:rounded-2xl overflow-hidden bg-gray-100 border-2 border-white shadow-lg group-hover:border-primary/20 transition-all flex-shrink-0">
                            <x-user-avatar :user="Auth::user()" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-10 max-w-[1600px] mx-auto w-full">
                @if (isset($header))
                    <div class="mb-8 lg:mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-black text-gray-900 tracking-tight leading-tight">{{ $header }}</h1>
                            @if(isset($breadcrump))
                                <div class="mt-2 flex items-center">
                                    {{ $breadcrump }}
                                </div>
                            @endif
                        </div>
                        @if(isset($actions))
                            <div class="flex items-center space-x-4">
                                {{ $actions }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="animate-in fade-in slide-in-from-bottom-6 duration-700 ease-out">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
