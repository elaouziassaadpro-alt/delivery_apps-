<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.driver')]
class Dashboard extends Component
{
    public $stats = [
        'today_earnings' => 0,
        'pending_tasks' => 0,
        'total_deliveries' => 0,
    ];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return;
        }

        // Stats calculations
        $this->stats['today_earnings'] = $driver->orders()
            ->where('status', 'delivered')
            ->whereDate('updated_at', Carbon::today())
            ->sum('driver_commission');

        $this->stats['pending_tasks'] = $driver->orders()
            ->where('status', 'pending')
            ->count();

        $this->stats['total_deliveries'] = $driver->orders()
            ->where('status', 'delivered')
            ->count();
    }

    public function getRecentActivitiesProperty()
    {
        $driver = Auth::user()->driver;
        
        if (!$driver) {
            return collect();
        }

        return $driver->orders()
            ->with(['recipient', 'bon'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.driver.dashboard', [
            'recentActivities' => $this->recent_activities
        ]);
    }
}
