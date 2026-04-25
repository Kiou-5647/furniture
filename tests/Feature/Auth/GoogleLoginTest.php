<?php

use App\Actions\Auth\HandleGoogleLoginAction;
use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Customer\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

uses(RefreshDatabase::class);

it('creates a new customer account when a new google user logs in', function () {
    // Arrange: Mock the Socialite User
    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-123');
    $googleUser->shouldReceive('getEmail')->andReturn('new@google.com');
    $googleUser->shouldReceive('getName')->andReturn('Google New User');

    // Act
    $action = new HandleGoogleLoginAction();
    $user = $action->execute($googleUser);

    // Assert
    expect($user->email)->toBe('new@google.com')
        ->and($user->google_id)->toBe('google-123')
        ->and($user->type)->toBe(UserType::Customer);

    $this->assertDatabaseHas('users', [
        'email' => 'new@google.com',
        'google_id' => 'google-123',
    ]);

    $this->assertDatabaseHas('customers', [
        'user_id' => $user->id,
        'full_name' => 'Google New User',
    ]);
});

it('links a google account to an existing user with the same email', function () {
    // Arrange: Existing user without google_id
    $user = User::factory()->create([
        'email' => 'existing@email.com',
        'google_id' => null,
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-456');
    $googleUser->shouldReceive('getEmail')->andReturn('existing@email.com');
    $googleUser->shouldReceive('getName')->andReturn('Existing User');

    // Act
    $action = new HandleGoogleLoginAction();
    $resultUser = $action->execute($googleUser);

    // Assert
    expect($resultUser->id)->toBe($user->id)
        ->and($resultUser->google_id)->toBe('google-456');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'google_id' => 'google-456',
    ]);
});

it('returns the existing user if they already have a google_id', function () {
    // Arrange
    $user = User::factory()->create([
        'google_id' => 'google-789',
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-789');
    $googleUser->shouldReceive('getEmail')->andReturn('user@google.com');
    $googleUser->shouldReceive('getName')->andReturn('User Name');

    // Act
    $action = new HandleGoogleLoginAction();
    $resultUser = $action->execute($googleUser);

    // Assert
    expect($resultUser->id)->toBe($user->id);
});
