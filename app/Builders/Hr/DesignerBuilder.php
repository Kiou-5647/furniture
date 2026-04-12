<?php

namespace App\Builders\Hr;

use Illuminate\Database\Eloquent\Builder;

class DesignerBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('full_name', 'ilike', "%{$search}%")
                ->orWhere('phone', 'ilike', "%{$search}%");
        });
    }

    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function byActiveStatus(bool $isActive): self
    {
        return $this->where('is_active', $isActive);
    }

    public function hostDesigners(): self
    {
        return $this->whereNotNull('employee_id');
    }

    public function freelancers(): self
    {
        return $this->whereNull('employee_id');
    }
}
