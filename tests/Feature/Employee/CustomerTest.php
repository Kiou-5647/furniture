<?php

use App\Models\Auth\User;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    // Seed Geodata for CustomerFactory
    $this->seed(\Database\Seeders\GeodataSeeder::class);
    $this->seed(\Database\Seeders\ProvinceRegionSeeder::class);

    // Create permissions first
    \App\Models\Auth\Permission::create(['name' => 'Xem khách hàng']);
    \App\Models\Auth\Permission::create(['name' => 'Quản lý khách hàng']);

    $this->admin = User::factory()->create([
        'type' => \App\Enums\UserType::Employee,
        'is_active' => true,
    ]);
    
    $this->admin->givePermissionTo('Xem khách hàng');
    $this->admin->givePermissionTo('Quản lý khách hàng');
});

it('can list customers', function () {
    $customer = Customer::factory()->create([
        'full_name' => 'Test Customer',
        'total_spent' => 100000,
    ]);

    $this->actingAs($this->admin)
        ->get(route('employee.customers.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('employee/customers/Index'));
});

it('can show customer profile and recent orders', function () {
    $customer = Customer::factory()->create();
    $user = $customer->user;
    
    \App\Models\Sales\Order::factory()->count(3)->create([
        'customer_id' => $user->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('employee.customers.show', $customer->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('employee/customers/Show'));
});

it('can deactivate a customer account', function () {
    $customer = Customer::factory()->create();
    $user = $customer->user;

    $this->actingAs($this->admin)
        ->post(route('employee.customers.deactivate', $customer->id))
        ->assertRedirect();

    expect($user->fresh()->is_active)->toBeFalse();
});

it('prevents deactivated users from accessing the site', function () {
    $user = User::factory()->create([
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('login'));
});
