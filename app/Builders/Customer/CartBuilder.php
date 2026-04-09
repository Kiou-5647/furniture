<?php

namespace App\Builders\Customer;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CartBuilder extends Builder
{
    public function open(): self
    {
        return $this->where('status', 'open');
    }

    public function abandoned(): self
    {
        return $this->where('status', 'abandoned');
    }

    public function checkedOut(): self
    {
        return $this->where('status', 'checked_out');
    }

    public function forUser(User $user): self
    {
        return $this->where('user_id', $user->id);
    }

    public function olderThan(Carbon $date): self
    {
        return $this->where('updated_at', '<', $date);
    }
}
