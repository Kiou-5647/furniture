<?php

namespace App\Models\Public;

use App\Builders\Customer\CartBuilder;
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

    public static function getOrCreate(?User $user = null, ?string $sessionId = null): Cart
    {
        $query = Cart::query();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return Cart::create();
        }

        return $query->first() ?? Cart::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
        ]);
    }

    public function calculateTotal(): float
    {
        return $this->items->sum(function (CartItem $item) {
            return $item->getEffectivePrice() * $item->quantity;
        });
    }

    public function clear(): void
    {
        $this->items()->delete();
    }
}
