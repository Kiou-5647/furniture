<?php

namespace App\Models\Customer;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'customers';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'avatar_path',
        'total_spent',
        'loyalty_points',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'loyalty_points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
