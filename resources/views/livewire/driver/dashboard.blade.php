<div>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-primary/10 rounded-2xl">
                <i data-lucide="layout-dashboard" class="w-6 h-6 text-primary"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Driver Dashboard</h1>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Today's Earnings -->
            <div class="bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="wallet" class="w-24 h-24 text-primary"></i>
                </div>
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                        <i data-lucide="banknote" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full">Today</span>
                </div>
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1 relative z-10">Total Earnings</h3>
                <p class="text-3xl font-black text-gray-900 relative z-10">{{ number_format($stats['today_earnings'], 2) }} <span class="text-sm font-medium text-gray-400">DH</span></p>
            </div>

            <!-- Pending Deliveries -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="clock" class="w-24 h-24 text-gray-900"></i>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                        <i data-lucide="package-search" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 bg-amber-50 px-3 py-1 rounded-full">In Progress</span>
                </div>
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Pending Task</h3>
                <p class="text-3xl font-black text-gray-900">{{ $stats['pending_tasks'] }} <span class="text-sm font-medium text-gray-400">Orders</span></p>
            </div>

            <!-- Total Deliveries -->
            <div class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="check-circle" class="w-24 h-24 text-green-600"></i>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-green-500/20">
                        <i data-lucide="truck" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-600 bg-green-50 px-3 py-1 rounded-full">Completed</span>
                </div>
                <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Deliveries</h3>
                <p class="text-3xl font-black text-gray-900">{{ $stats['total_deliveries'] }} <span class="text-sm font-medium text-gray-400">Success</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activities -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Recent Deliveries</h2>
                    <a href="{{ route('driver.bons.index') }}" wire:navigate class="text-xs font-bold text-primary hover:underline flex items-center space-x-1 uppercase tracking-widest">
                        <span>View All</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm">
                    <div class="divide-y divide-gray-50">
                        @forelse($recentActivities as $activity)
                            <div class="p-5 hover:bg-gray-50/50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center group-hover:bg-white transition-colors border border-transparent group-hover:border-gray-100">
                                        <i data-lucide="{{ $activity->status === 'delivered' ? 'check-circle' : 'clock' }}" 
                                           class="w-6 h-6 {{ $activity->status === 'delivered' ? 'text-green-500' : 'text-amber-500' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-gray-900">#{{ $activity->code }}</h4>
                                        <p class="text-xs text-gray-400 font-medium line-clamp-1 uppercase tracking-tighter">{{ $activity->recipient->name ?? 'Guest' }} • {{ $activity->recipient->city ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-gray-900">{{ number_format($activity->driver_commission, 2) }} <span class="text-[10px] text-gray-400">DH</span></p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ $activity->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100/50">
                                    <i data-lucide="package" class="w-8 h-8 text-gray-200"></i>
                                </div>
                                <h3 class="text-sm font-bold text-gray-400">No recent activity detected</h3>
                                <p class="text-xs text-gray-400 mt-1 max-w-[200px] mx-auto">Click "Scan New Order" to start your first delivery of the day.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Profile Summary -->
            <div class="space-y-8">
                <div class="bg-[#0f172a] rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-[#0f172a]/20 group/card">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/30 rounded-full blur-3xl group-hover/card:bg-primary/40 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="flex items-center space-x-4 mb-8">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white/10 shadow-lg bg-white/10 p-0.5">
                                <x-user-avatar :user="Auth::user()" class="w-full h-full object-cover rounded-[0.8rem]" />
                            </div>
                            <div>
                                <h3 class="font-black text-lg tracking-tight">{{ Auth::user()->name }}</h3>
                                <p class="text-[10px] text-primary font-black uppercase tracking-widest">Duty Status: Online</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <a href="{{ route('driver.bons.index') }}" 
                               wire:navigate
                               class="flex items-center justify-center space-x-3 w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-primary/20 group">
                                <i data-lucide="qr-code" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                                <span>Scan New Order</span>
                            </a>
                            <a href="{{ route('driver.profile') }}" 
                               wire:navigate
                               class="flex items-center justify-center space-x-3 w-full bg-white/5 hover:bg-white/10 text-white/80 hover:text-white font-bold py-4 rounded-2xl transition-all border border-white/5">
                                <i data-lucide="user" class="w-5 h-5 opacity-50"></i>
                                <span>My Profile</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Next Destination / Tip -->
                <div class="bg-primary/5 border border-primary/10 rounded-[2rem] p-6 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-primary/10 -rotate-12">
                        <i data-lucide="lightbulb" class="w-20 h-20"></i>
                    </div>
                    <div class="flex items-start space-x-4 relative z-10">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm border border-primary/10 flex-shrink-0">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 mb-1 leading-tight">Pro Tip for Drivers</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">Always verify the QR code color before scanning. A green frame indicates a valid system code.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
