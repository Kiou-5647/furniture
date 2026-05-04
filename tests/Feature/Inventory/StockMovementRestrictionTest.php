<?php

use App\Data\Inventory\StockMovementFilterData;
use App\Models\Auth\User;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Services\Inventory\StockMovementService;
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

    $this->service = app(StockMovementService::class);
    $this->filter = new StockMovementFilterData(
        type: null,
        location_id: null,
        variant_id: null,
        search: null,
        date_from: null,
        date_to: null,
        order_by: 'created_at',
        order_direction: 'desc',
        per_page: 15
    );
});

test('administrators can see all stock movements', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Quản trị viên');

    StockMovement::factory()->count(3)->create();

    $this->actingAs($admin);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(3);
});

test('managers can see all stock movements', function () {
    $manager = User::factory()->create();
    $manager->assignRole('Quản lý');

    StockMovement::factory()->count(3)->create();

    $this->actingAs($manager);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(3);
});

test('employees with store_location_id can see movements in that location', function () {
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => $location->id,
        'warehouse_location_id' => null,
    ]);

    StockMovement::factory()->create(['location_id' => $location->id]);
    StockMovement::factory()->create(['location_id' => $otherLocation->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(1);
    expect($result->items()[0]->location_id)->toBe($location->id);
});

test('employees with warehouse_location_id can see movements in that location', function () {
    $location = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => null,
        'warehouse_location_id' => $location->id,
    ]);

    StockMovement::factory()->create(['location_id' => $location->id]);
    StockMovement::factory()->create(['location_id' => $otherLocation->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(1);
    expect($result->items()[0]->location_id)->toBe($location->id);
});

test('employees with both store and warehouse locations can see movements from either', function () {
    $store = Location::factory()->create();
    $warehouse = Location::factory()->create();
    $other = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => $store->id,
        'warehouse_location_id' => $warehouse->id,
    ]);

    StockMovement::factory()->create(['location_id' => $store->id]);
    StockMovement::factory()->create(['location_id' => $warehouse->id]);
    StockMovement::factory()->create(['location_id' => $other->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(2);
});

test('employees with no locations see no movements', function () {
    $location = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => null,
        'warehouse_location_id' => null,
    ]);

    StockMovement::factory()->create(['location_id' => $location->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(0);
});

test('employees do not see movements from locations they are not assigned to', function () {
    $myLocation = Location::factory()->create();
    $theirLocation = Location::factory()->create();

    $employeeUser = User::factory()->create();
    Employee::factory()->create([
        'user_id' => $employeeUser->id,
        'store_location_id' => $myLocation->id,
        'warehouse_location_id' => null,
    ]);

    StockMovement::factory()->create(['location_id' => $myLocation->id]);
    StockMovement::factory()->create(['location_id' => $theirLocation->id]);

    $this->actingAs($employeeUser);
    $result = $this->service->getFiltered($this->filter);

    expect($result->total())->toBe(1);
    expect($result->items()[0]->location_id)->toBe($myLocation->id);
});
