<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    public $search = '';

    public function view($id)
    {
        $order = Order::with(['bon.user', 'recipient'])->findOrFail($id);

        $this->dispatch('open-order-details', order: [
            'id'             => $order->id,
            'code'           => $order->code,
            'status'         => $order->status,
            'price'          => $order->price,
            'location'       => $order->location,
            'lat'            => $order->lat,
            'lng'            => $order->lng,
            'payment_status' => $order->payment_status ?? null,
            'client'         => $order->bon?->user?->name ?? 'N/A',
            'recipient'      => $order->recipient ? [
                'first_name' => $order->recipient->first_name,
                'last_name'  => $order->recipient->last_name,
                'phone'      => $order->recipient->phone,
                'email'      => $order->recipient->email,
            ] : null,
        ]);
    }

    public function edit($id)
    {
        // placeholder – extend as needed
    }

    public function delete($id)
    {
        Order::findOrFail($id)->delete();
        $this->dispatch('order-deleted');
    }

    public function render()
    {
        return view('livewire.admin.orders-table', [
            'orders' => Order::with(['bon.user', 'recipient'])
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('code', 'like', '%' . $this->search . '%')
                          ->orWhereHas('bon.user', function ($sq) {
                              $sq->where('name', 'like', '%' . $this->search . '%');
                          });
                    });
                })
                ->latest()
                ->paginate(10)
        ]);
    }
}