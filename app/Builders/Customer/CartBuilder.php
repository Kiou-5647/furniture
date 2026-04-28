<?php

namespace App\Builders\Customer;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CartBuilder extends Builder
{
    public function forUser(User $user): self
    {
        return $this->where('user_id', $user->id);
    }

    public function forSession(string $sessionId): self
    {
        return $this->where('session_id', $sessionId);
    }

    public function olderThan(Carbon $date): self
    {
        return $this->where('updated_at', '<', $date);
    }
}
