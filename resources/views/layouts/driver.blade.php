<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Driver</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo.png') }}">
    
    <!-- Leaflet (Optional for Driver Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#f8fafc] text-[#1e293b]">
    <div class="flex min-h-screen relative bg-gray-50/50" 
         x-data="{ mobileMenuOpen: false }">
        
        <!-- Sidebar for Desktop -->
        <aside class="hidden lg:flex w-72 bg-[#0f172a] text-white flex-col fixed h-full z-40 border-r border-white/5 shadow-2xl">
            <div class="p-8">
                <div class="flex items-center space-x-3 group cursor-pointer">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary/80 rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(var(--primary-rgb),0.3)]">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" class="w-6 h-6">
                    </div>
                    <span class="text-xl font-black tracking-tight whitespace-nowrap">
                        DRIVER<span class="text-primary">.</span>
                    </span>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-2 mt-4">
                <p class="px-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 mb-4">Operations</p>
                
                <a href="{{ route('driver.dashboard') }}" 
                   wire:navigate
                   class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl group {{ request()->routeIs('driver.dashboard') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 text-gray-400 hover:text-white' }} transition-all duration-300">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('driver.dashboard') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}"></i>
                    <span class="font-bold text-sm">Dashboard</span>
                </a>

                <a href="{{ route('driver.deliveries') }}" 
                   wire:navigate
                   class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl group {{ request()->routeIs('driver.deliveries') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 text-gray-400 hover:text-white' }} transition-all duration-300">
                    <i data-lucide="truck" class="w-5 h-5 {{ request()->routeIs('driver.deliveries') ? 'drop-shadow-[0_0_8px_rgba(var(--primary-rgb),0.8)]' : '' }}"></i>
                    <span class="font-bold text-sm">Active Deliveries</span>
                </a>

                <a href="#" 
                   class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl group hover:bg-white/5 text-gray-400 hover:text-white transition-all duration-300">
                    <i data-lucide="history" class="w-5 h-5"></i>
                    <span class="font-bold text-sm">My History</span>
                </a>

                <p class="px-4 pt-8 text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 mb-4">Account</p>

                <a href="{{ route('driver.profile') }}" 
                   wire:navigate
                   class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl group {{ request()->routeIs('driver.profile') ? 'bg-primary/10 text-primary shadow-[inset_4px_0_0_0_rgba(var(--primary-rgb),1)]' : 'hover:bg-white/5 text-gray-400 hover:text-white' }} transition-all duration-300">
                    <i data-lucide="user-circle" class="w-5 h-5"></i>
                    <span class="font-bold text-sm">Profile Settings</span>
                </a>
            </nav>

            <div class="p-6 border-t border-white/5">
                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="w-full flex items-center space-x-3 px-4 py-4 rounded-2xl text-red-400 hover:bg-red-500/10 transition-all group">
                    <i data-lucide="log-out" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-bold text-sm">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-72 pb-24 lg:pb-0">
            <!-- Header -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 lg:px-10 sticky top-0 z-30">
                <div class="flex items-center lg:hidden">
                    <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                        <img src="{{ asset('assets/logo/logo.png') }}" alt="Logo" class="w-6 h-6">
                    </div>
                </div>

                <div class="flex-1 px-4 lg:hidden">
                    <span class="font-black text-lg tracking-tight uppercase">Driver <span class="text-primary">App</span></span>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-[#0f172a]">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-primary font-black uppercase tracking-widest mt-0.5">Online • Duty</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 border-2 border-white shadow-md">
                        <x-user-avatar :user="Auth::user()" class="w-full h-full object-cover" />
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-10 max-w-7xl mx-auto w-full">
                @if (isset($header))
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ $header }}</h1>
                            @if(isset($breadcrump))
                                <div class="mt-1 flex items-center text-xs text-gray-400">
                                    {{ $breadcrump }}
                                </div>
                            @endif
                        </div>
                        @if(isset($actions))
                            <div class="flex items-center">
                                {{ $actions }}
                            </div>
                        @endif
                    </div>
                @endif

                <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Bottom Navigation for Mobile -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 px-6 pb-6 pt-3 flex justify-between items-center z-50 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
            <a href="{{ route('driver.dashboard') }}" 
               class="flex flex-col items-center space-y-1 {{ request()->routeIs('driver.dashboard') ? 'text-primary' : 'text-gray-400' }}">
                <div class="p-2 {{ request()->routeIs('driver.dashboard') ? 'bg-primary/10 rounded-xl' : '' }}">
                    <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-tighter">Home</span>
            </a>

            <a href="{{ route('driver.deliveries') }}" 
               class="flex flex-col items-center space-y-1 {{ request()->routeIs('driver.deliveries') ? 'text-primary' : 'text-gray-400' }}">
                <div class="p-2 {{ request()->routeIs('driver.deliveries') ? 'bg-primary/10 rounded-xl' : '' }}">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-tighter">Deliveries</span>
            </a>

            <a href="#" class="flex flex-col items-center space-y-1 text-gray-400">
                <div class="p-2">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-tighter">History</span>
            </a>

            <a href="{{ route('driver.profile') }}" 
               class="flex flex-col items-center space-y-1 {{ request()->routeIs('driver.profile') ? 'text-primary' : 'text-gray-400' }}">
                <div class="p-2 {{ request()->routeIs('driver.profile') ? 'bg-primary/10 rounded-xl' : '' }}">
                    <i data-lucide="user-circle" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-tighter">Profile</span>
            </a>

            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex flex-col items-center space-y-1 text-red-400">
                <div class="p-2">
                    <i data-lucide="log-out" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-tighter">Exit</span>
            </button>
        </nav>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
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

