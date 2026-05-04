<?php

namespace Tests\Feature\Employee;

use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Hr\Designer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions since we are using RefreshDatabase
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_super_admin_can_see_all_bookings()
    {
        $admin = User::factory()->create(['is_active' => true, 'type' => \App\Enums\UserType::Employee]);
        $admin->assignRole('Quản trị viên');

        $designer1 = Designer::factory()->create();
        $designer2 = Designer::factory()->create();

        Booking::factory()->count(2)->create(['designer_id' => $designer1->id]);
        Booking::factory()->count(3)->create(['designer_id' => $designer2->id]);

        $service = app(\App\Services\Booking\BookingService::class);
        $filter = \App\Data\Booking\BookingFilterData::fromRequest(request());
        
        $bookings = $service->getFiltered($filter, $admin);

        $this->assertEquals(5, $bookings->total());
    }

    public function test_designer_can_only_see_their_own_bookings()
    {
        $user = User::factory()->create(['is_active' => true, 'type' => \App\Enums\UserType::Employee]);
        $user->assignRole('Nhân viên');
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $otherDesigner = Designer::factory()->create();

        Booking::factory()->count(2)->create(['designer_id' => $designer->id]);
        Booking::factory()->count(3)->create(['designer_id' => $otherDesigner->id]);

        $service = app(\App\Services\Booking\BookingService::class);
        $filter = \App\Data\Booking\BookingFilterData::fromRequest(request());
        
        $bookings = $service->getFiltered($filter, $user);

        $this->assertEquals(2, $bookings->total());
    }

    public function test_regular_employee_cannot_see_any_bookings_if_not_designer()
    {
        $user = User::factory()->create(['is_active' => true, 'type' => \App\Enums\UserType::Employee]);
        $user->assignRole('Nhân viên');

        Booking::factory()->count(5)->create();

        $service = app(\App\Services\Booking\BookingService::class);
        $filter = \App\Data\Booking\BookingFilterData::fromRequest(request());
        
        $bookings = $service->getFiltered($filter, $user);

        $this->assertEquals(0, $bookings->total());
    }

    public function test_regular_employee_cannot_access_booking_they_do_not_own()
    {
        $user = User::factory()->create(['is_active' => true, 'type' => \App\Enums\UserType::Employee]);
        $user->assignRole('Nhân viên');
        
        $otherDesigner = Designer::factory()->create();
        $booking = Booking::factory()->create(['designer_id' => $otherDesigner->id]);

        $this->actingAs($user)
            ->get(route('employee.booking.show', $booking))
            ->assertStatus(404); // Service uses findOrFail and applyRoleFilter
    }

    public function test_designer_can_access_their_own_booking()
    {
        $user = User::factory()->create(['is_active' => true, 'type' => \App\Enums\UserType::Employee]);
        $user->assignRole('Nhân viên');
        $designer = Designer::factory()->create(['user_id' => $user->id]);

        $booking = Booking::factory()->create(['designer_id' => $designer->id]);

        $this->actingAs($user)
            ->get(route('employee.booking.show', $booking))
            ->assertStatus(200);
    }
}
