<?php

namespace App\Actions\Customer;

use App\Models\Booking\Booking;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class UpdateCustomerTotalSpentAction
{
    public function execute(Order|Booking $record): void
    {
        $customer = $record->customer;

        if (! $customer) {
            return;
        }

        $amount = $record instanceof Order 
            ? $record->total_amount 
            : $record->total_price;

        DB::table('customers')
            ->where('id', $customer->id)
            ->increment('total_spent', $amount);

        $customer->refresh();
    }
}
