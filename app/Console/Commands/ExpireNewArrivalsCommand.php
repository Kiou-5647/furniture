<?php

namespace App\Console\Commands;

use App\Models\Product\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireNewArrivalsCommand extends Command
{
    protected $signature = 'products:expire-new-arrivals';
    protected $description = 'Set is_new_arrival to false for products that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $affectedRows = Product::where('is_new_arrival', true)
            ->where('new_arrival_until', '<', $now)
            ->update(['is_new_arrival' => false]);

        $this->info("Successfully expired {$affectedRows} new arrival products.");
    }
}
