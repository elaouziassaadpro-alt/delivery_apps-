<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use App\Models\Bon;




#[Layout('layouts.driver')]
class Create extends Component

{
    public $bon = true;
    public $orders_page = false;
    public $code;
    public $user_id;
    public $status;
    public $payment_status;
    public $payment_method;
    public $delivery_type;
    public $pickup_date;
    public $price;
    public $driver_commission;
    public $commission;
    public $weight;
    public $dimensions_length;
    public $dimensions_width;
    public $dimensions_height;
    public $notes;

    public $orders;
    public $qrCode;
    public $order;

    public $activeBonId = null;

    public function generateQrCode()
    {
        if (!$this->code) {
            $this->addError('code', 'Code is empty.');
            return;
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($this->code);

        return $qrUrl;
    }
    public function mount()
    {
        
    }
    public function createBon()
    {
        $bon = Bon::create([
            'code' => $this->code,
            'user_id' => Auth::user()->id,
            'status' => $this->status ?? 'pending',
            'payment_status' => $this->payment_status ?? 'unpaid',
            'payment_method' => $this->payment_method ?? 'cash',
            'delivery_type' => $this->delivery_type ?? 'standard',
            'pickup_date' => $this->pickup_date,
            'price' => $this->price ?? 0,
            'driver_commission' => $this->driver_commission ?? 0,
            'commission' => $this->commission ?? 0,
            'weight' => $this->weight,
            'dimensions_length' => $this->dimensions_length,
            'dimensions_width' => $this->dimensions_width,
            'dimensions_height' => $this->dimensions_height,
            'notes' => $this->notes,
        ]);

        $this->activeBonId = $bon->id;
        
        $this->bon = false;
        $this->orders_page = false;

        session()->flash('success', 'Bon created successfully! Please scan or search orders below to add them directly to this Bon.');
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

    public function findOrder()
    {
        if (!$this->qrCode) {
            $this->order = null;
            return;
        }

        $this->order = Order::with(['recipient', 'bon'])
            ->where('code', $this->qrCode)
            ->first();

        if (!$this->order) {
            session()->flash('error', 'Order not found.');
        } else {
            session()->forget('error');
        }
    }

    public function updatedQrCode($value)
    {
        $this->findOrder();
    }

    public function searchOrder()
    {
        $this->findOrder();
    }

    public function assignToMe()
    {
        $driver = Auth::user()->driver;
        
        if (!$driver) {
            session()->flash('error', 'Driver profile not found.');
            return;
        }

        if ($this->order && strtolower($this->order->status) === 'pending') {
            $this->order->update([
                'driver_id' => $driver->id,
                'status' => 'assigned',
                'bon_driver_id' => $this->activeBonId ?? null,
            ]);
            
            session()->flash('success', 'Order #' . $this->order->code . ' has been assigned to you.');
            
            $this->loadOrders();
            $this->order->refresh();
        } else {
            session()->flash('error', 'Order cannot be assigned. It might not be pending.');
        }
    }

    public function markAsDelivered()
    {
        if (!$this->order || strtolower($this->order->status) === 'delivered') {
            return;
        }

        $this->order->update(['status' => 'delivered']);
        
        session()->flash('success', 'Order #' . $this->order->code . ' marked as delivered.');
        
        $this->loadOrders();
        $this->order->refresh();
    }

    public function render()
    {
        return view('livewire.driver.create');
    }
}