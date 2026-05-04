<?php

use App\Data\Inventory\StockTransferFilterData;
use App\Models\Auth\User;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Services\Inventory\StockTransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Create required roles
    $roles = ['Quản trị viên', 'Quản lý'];
    foreach ($roles as $role) {
        if (!\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => $role]);
        }
    }
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create required location data for LocationFactory
    $province = \App\Models\Setting\Province::create([
        'province_code' => 'P1',
        'name' => 'Province 1',
        'short_name' => 'P1',
        'code' => 'P1',
        'place_type' => 1,
    ]);
    \App\Models\Setting\Ward::create([
        'ward_code' => 'W1',
        'province_code' => 'P1',
        'name' => 'Ward 1',
    ]);

    $this->service = app(StockTransferService::class);
    $this->filter = new StockTransferFilterData(
        status: null,
        from_location_id: null,
        to_location_id: null,
        search: null,
        date_from: null,
        date_to: null,
        order_by: 'created_at',
        order_direction: 'desc',
        per_page: 15
    );
});

test('administrators can see all stock transfers', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Quản trị viên');

    StockTransfer::factory()->count(3)->create();

    $this->actingAs($admin);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(3);
});

test('managers can see all stock transfers', function () {
    $manager = User::factory()->create();
    $manager->assignRole('Quản lý');

    StockTransfer::factory()->count(3)->create();

    $this->actingAs($manager);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(3);
});

test('employees with store_location_id can see transfers where from or to is their location', function () {
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => $location->id,
        'warehouse_location_id' => null,
    ]);

    // Case 1: From their location
    StockTransfer::factory()->create(['from_location_id' => $location->id, 'to_location_id' => $otherLocation->id]);
    // Case 2: To their location
    StockTransfer::factory()->create(['from_location_id' => $otherLocation->id, 'to_location_id' => $location->id]);
    // Case 3: Neither (should be hidden)
    StockTransfer::factory()->create(['from_location_id' => $otherLocation->id, 'to_location_id' => $otherLocation->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(2);
});

test('employees with warehouse_location_id can see transfers where from or to is their location', function () {
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => null,
        'warehouse_location_id' => $location->id,
    ]);

    // Case 1: From their location
    StockTransfer::factory()->create(['from_location_id' => $location->id, 'to_location_id' => $otherLocation->id]);
    // Case 2: To their location
    StockTransfer::factory()->create(['from_location_id' => $otherLocation->id, 'to_location_id' => $location->id]);
    // Case 3: Neither (should be hidden)
    StockTransfer::factory()->create(['from_location_id' => $otherLocation->id, 'to_location_id' => $otherLocation->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(2);
});

test('employees with both store and warehouse locations can see transfers from/to either', function () {
    $store = Location::factory()->create();
    $warehouse = Location::factory()->create();
    $other = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => $store->id,
        'warehouse_location_id' => $warehouse->id,
    ]);

    // Store: from
    StockTransfer::factory()->create(['from_location_id' => $store->id, 'to_location_id' => $other->id]);
    // Warehouse: to
    StockTransfer::factory()->create(['from_location_id' => $other->id, 'to_location_id' => $warehouse->id]);
    // Neither
    StockTransfer::factory()->create(['from_location_id' => $other->id, 'to_location_id' => $other->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(2);
});

test('employees with no locations see no transfers', function () {
    $location = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => null,
        'warehouse_location_id' => null,
    ]);

    StockTransfer::factory()->create(['from_location_id' => $location->id, 'to_location_id' => $location->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(0);
});
