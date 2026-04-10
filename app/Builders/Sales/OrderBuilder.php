<?php

namespace App\Builders\Sales;

use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends Builder
{
    public function pending(): self
    {
        return $this->where('status', 'pending');
    }

    public function processing(): self
    {
        return $this->where('status', 'processing');
    }

    public function completed(): self
    {
        return $this->where('status', 'completed');
    }

    public function cancelled(): self
    {
        return $this->where('status', 'cancelled');
    }
}
