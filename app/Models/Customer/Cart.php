<?php

namespace App\Models\Customer;

use App\Builders\Customer\CartBuilder;
use App\Enums\CartStatus;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \App\Builders\Customer\CartBuilder|Cart query()
 */
class Cart extends Model
{
    use HasUuids;

    protected $table = 'carts';

    protected function casts(): array
    {
        return [
            'status' => CartStatus::class,
        ];
    }

    public function newEloquentBuilder($query): CartBuilder
    {
        return new CartBuilder($query);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(CartItem::class)->with('purchasable');
    }

    public static function getOrCreateForUser(User $user): Cart
    {
        return Cart::query()
            ->forUser($user)
            ->open()
            ->first() ?? Cart::create([
                'user_id' => $user->id,
                'status' => CartStatus::Open,
            ]);
    }

    public function calculateTotal(): float
    {
        return $this->items->sum(function (CartItem $item) {
            return (float) $item->unit_price * $item->quantity;
        });
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function markAsCheckedOut(): void
    {
        $this->updateQuietly(['status' => CartStatus::CheckedOut]);
    }

    public function markAsAbandoned(): void
    {
        $this->updateQuietly(['status' => CartStatus::Abandoned]);
    }
}
