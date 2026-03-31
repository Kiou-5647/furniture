<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Auth{
/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutRole($roles, ?string $guard = null)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models\Auth{
/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutPermission($permissions)
 */
	class Role extends \Eloquent {}
}

namespace App\Models\Auth{
/**
 * @property string $id
 * @property \App\Enums\UserType $type
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property bool $is_verified
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string|null $last_login_ip
 * @property \Carbon\CarbonImmutable|null $last_login_at
 * @property string|null $remember_token
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Carbon\CarbonImmutable|null $two_factor_confirmed_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Models\Customer\Customer|null $customer
 * @property-read \App\Models\Employee\Employee|null $employee
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Auth\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Vendor\Vendor|null $vendor
 * @property-read \App\Models\Vendor\VendorUser|null $vendorUser
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models\Customer{
/**
 * @property string $id
 * @property string $user_id
 * @property string|null $full_name
 * @property string|null $phone
 * @property numeric $total_spent
 * @property int $loyalty_points
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Customer\CustomerAddress> $addresses
 * @property-read int|null $addresses_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Auth\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLoyaltyPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereTotalSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUserId($value)
 */
	class Customer extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models\Customer{
/**
 * @property string $id
 * @property string $customer_id
 * @property string $type
 * @property string|null $delivery_instructions
 * @property string|null $province_code
 * @property string|null $ward_code
 * @property string|null $province_name
 * @property string|null $ward_name
 * @property array<array-key, mixed> $address_data
 * @property bool $is_default
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Models\Customer\Customer $customer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereAddressData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereDeliveryInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereProvinceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereProvinceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereWardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereWardName($value)
 */
	class CustomerAddress extends \Eloquent {}
}

namespace App\Models\Employee{
/**
 * @property string $id
 * @property string|null $manager_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \App\Models\Employee\Employee|null $manager
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models\Employee{
/**
 * @property string $id
 * @property string $user_id
 * @property string|null $department_id
 * @property string $full_name
 * @property string|null $phone
 * @property \Carbon\CarbonImmutable|null $hire_date
 * @property \Carbon\CarbonImmutable|null $termination_date
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Models\Employee\Department|null $department
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Auth\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 */
	class Employee extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models\Product{
/**
 * @method static CategoryBuilder|Category query()
 * @method static CategoryBuilder|Category byCategoryGroup(Lookup $group)
 * @method static CategoryBuilder|Category byProductType(ProductType $type)
 * @method static CategoryBuilder|Category search(string $search)
 * @property string $id
 * @property string|null $group_id
 * @property \App\Enums\ProductType|null $product_type
 * @property string $display_name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property array<array-key, mixed> $metadata
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Models\Setting\Lookup|null $group
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting\Lookup> $rooms
 * @property-read int|null $rooms_count
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category newModelQuery()
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereCreatedAt($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereDeletedAt($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereDescription($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereDisplayName($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereGroupId($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereId($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereIsActive($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereMetadata($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereProductType($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereSlug($value)
 * @method static \App\Builders\Product\CategoryBuilder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 */
	class Category extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models\Product{
/**
 * @method static CollectionBuilder|Collection query()
 * @method static CollectionBuilder|Collection search(string $search)
 * @method static CollectionBuilder|Collection active()
 * @method static CollectionBuilder|Collection featured()
 * @property string $id
 * @property string $display_name
 * @property string $slug
 * @property bool $is_active
 * @property bool $is_featured
 * @property string|null $description
 * @property array<array-key, mixed> $metadata
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection newModelQuery()
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collection onlyTrashed()
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereCreatedAt($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereDeletedAt($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereDescription($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereDisplayName($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereId($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereIsActive($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereIsFeatured($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereMetadata($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereSlug($value)
 * @method static \App\Builders\Product\CollectionBuilder<static>|Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collection withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collection withoutTrashed()
 */
	class Collection extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models\Setting{
/**
 * @method static LookupBuilder|Lookup query()
 * @method static LookupBuilder|Lookup byNamespace(LookupType $type)
 * @method static LookupBuilder|Lookup search(string $search)
 * @property string $id
 * @property \App\Enums\LookupType|null $namespace
 * @property string $slug
 * @property string $display_name
 * @property string|null $description
 * @property bool $is_active
 * @property array<array-key, mixed> $metadata
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup newModelQuery()
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup onlyTrashed()
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereCreatedAt($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereDeletedAt($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereDescription($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereDisplayName($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereId($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereIsActive($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereMetadata($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereNamespace($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereSlug($value)
 * @method static \App\Builders\Setting\LookupBuilder<static>|Lookup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup withoutTrashed()
 */
	class Lookup extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models\Vendor{
/**
 * @property string $id
 * @property string|null $verified_by
 * @property string $name
 * @property string|null $code
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $webhook_url
 * @property string|null $address
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_holder
 * @property array<array-key, mixed>|null $api_credentials
 * @property array<array-key, mixed> $shipping_regions
 * @property array<array-key, mixed> $tags
 * @property int $payment_terms_days
 * @property int $lead_time_days
 * @property numeric $minimum_order_amount
 * @property numeric|null $rating
 * @property int $total_orders
 * @property numeric $total_revenue
 * @property bool $is_active
 * @property bool $is_preferred
 * @property \Carbon\CarbonImmutable|null $verified_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vendor\VendorUser> $vendorUsers
 * @property-read int|null $vendor_users_count
 * @property-read \App\Models\Employee\Employee|null $verifiedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereApiCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBankAccountHolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBankAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereIsPreferred($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereLeadTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereMinimumOrderAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePaymentTermsDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereShippingRegions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTotalOrders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTotalRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWebhookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWebsite($value)
 */
	class Vendor extends \Eloquent {}
}

namespace App\Models\Vendor{
/**
 * @property string $id
 * @property string $user_id
 * @property string $vendor_id
 * @property string|null $full_name
 * @property string|null $phone
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Auth\User $user
 * @property-read \App\Models\Vendor\Vendor $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereVendorId($value)
 */
	class VendorUser extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

