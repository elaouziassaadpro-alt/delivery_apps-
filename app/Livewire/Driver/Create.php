<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;




#[Layout('layouts.driver')]
class Create extends Component

{
    public $orders;
    public $qrCode;
    public $order;

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $driver = Auth::user()->driver;
        
        if (!$driver) {
            $this->orders = collect();
            return;
        }

        $this->orders = $driver->orders()
            ->with(['recipient', 'bon'])
            ->latest()
            ->get();
    }

    public function updatedQrCode($value)
    {
        if (!$value) {
            $this->order = null;
            return;
        }

        $driver = Auth::user()->driver;
        
        if (!$driver) {
            session()->flash('error', 'Driver profile not found.');
            return;
        }

        $this->order = $driver->orders()
            ->where('code', $value)
            ->with(['recipient', 'bon'])
            ->first();

        if (!$this->order) {
            session()->flash('error', 'Order not found or not assigned to you.');
        } else {
            session()->forget('error');
        }
    }

    public function markAsDelivered()
    {
        if (!$this->order || $this->order->status === 'delivered') {
            return;
        }

        $this->order->update(['status' => 'delivered']);
        
        session()->flash('success', 'Order #' . $this->order->code . ' marked as delivered.');
        
        $this->loadOrders();
        $this->order = null;
        $this->qrCode = '';
    }

    public function render()
    {
        return view('livewire.driver.create');

    }
}