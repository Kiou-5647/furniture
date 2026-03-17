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
 * @property bool|null $is_active
 * @property bool|null $is_verified
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
 * @property string|null $avatar_path
 * @property numeric|null $total_spent
 * @property int|null $loyalty_points
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Customer\CustomerAddress> $addresses
 * @property-read int|null $addresses_count
 * @property-read \App\Models\Auth\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAvatarPath($value)
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
	class Customer extends \Eloquent {}
}

namespace App\Models\Customer{
/**
 * @property string $id
 * @property string|null $customer_id
 * @property string|null $type
 * @property string|null $delivery_instructions
 * @property string|null $province_code
 * @property string|null $ward_code
 * @property string|null $province_name
 * @property string|null $ward_name
 * @property array<array-key, mixed> $address_data
 * @property bool|null $is_default
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Customer\Customer|null $customer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereAddressData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCustomerId($value)
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
 * @property bool|null $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
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
 * @property string|null $avatar_path
 * @property \Carbon\CarbonImmutable|null $hire_date
 * @property \Carbon\CarbonImmutable|null $termination_date
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Employee\Department|null $department
 * @property-read \App\Models\Auth\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAvatarPath($value)
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
	class Employee extends \Eloquent {}
}

namespace App\Models\Setting{
/**
 * @property int $id
 * @property string $namespace
 * @property string|null $key
 * @property string $display_name
 * @property mixed|null $metadata
 * @property bool|null $is_active
 * @property bool|null $is_system
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup byNamespace(string $namespace)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup custom()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereNamespace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lookup whereUpdatedAt($value)
 */
	class Lookup extends \Eloquent {}
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
 * @property string|null $notes
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_holder
 * @property array<array-key, mixed>|null $api_credentials
 * @property array<array-key, mixed>|null $shipping_regions
 * @property array<array-key, mixed>|null $tags
 * @property int|null $payment_terms_days
 * @property int|null $lead_time_days
 * @property numeric|null $minimum_order_amount
 * @property numeric|null $rating
 * @property int|null $total_orders
 * @property numeric|null $total_revenue
 * @property bool|null $is_active
 * @property bool|null $is_preferred
 * @property \Carbon\CarbonImmutable|null $verified_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereNotes($value)
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
 * @property string|null $vendor_id
 * @property string|null $full_name
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Auth\User $user
 * @property-read \App\Models\Vendor\Vendor|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereAvatarPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorUser whereVendorId($value)
 */
	class VendorUser extends \Eloquent {}
}

