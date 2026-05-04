<?php

namespace Database\Seeders;

use App\Models\Customer\Customer;
use App\Models\Customer\Review;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variants = ProductVariant::all();
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please seed customers first.');
            return;
        }

        $this->command->info('Seeding reviews for ' . $variants->count() . ' variants...');

        foreach ($variants as $variant) {
            $numReviews = rand(10, 20);
            
            // We need to pick unique customers for this variant to avoid unique constraint violation
            // If we have fewer customers than required reviews, we take all available customers
            $availableCustomers = $customers->shuffle()->take($numReviews);

            foreach ($availableCustomers as $customer) {
                Review::firstOrCreate(
                    [
                        'variant_id' => $variant->id,
                        'customer_id' => $customer->id,
                    ],
                    Review::factory()->make()->toArray()
                );
            }
        }

        $this->command->info('Reviews seeded successfully!');
    }
}
