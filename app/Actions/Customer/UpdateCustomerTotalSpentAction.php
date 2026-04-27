<?php

namespace App\Actions\Customer;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class UpdateCustomerTotalSpentAction
{
    public function execute(Order $order): void
    {
        $customer = $order->customer->customer;

        if (! $customer) {
            return;
        }

        DB::table('customers')
            ->where('id', $customer->id)
            ->increment('total_spent', $order->total_amount);

        $customer->refresh();
    }
}
