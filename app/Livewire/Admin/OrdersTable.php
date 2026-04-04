<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    public $search = '';


    public function render()
    {
        return view('livewire.admin.orders-table', [
            'orders' => Order::with('bon.user')
                ->whereHas('bon.user', function($q) {
                    $q->where('role', 'client');
                })
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('code', 'like', '%' . $this->search . '%')
                          ->orWhereHas('bon.user', function($sq) {
                              $sq->where('name', 'like', '%' . $this->search . '%');
                          });
                    });
                })
                ->latest()
                ->paginate(10)
        ]);
    }
}