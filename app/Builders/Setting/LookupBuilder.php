<?php

namespace App\Builders\Setting;

use Illuminate\Database\Eloquent\Builder;

class LookupBuilder extends Builder
{
    public function byNamespace(string $namespace): self
    {
        return $this->where('namespace', $namespace);
    }

    public function search(string $search): self
    {
        return $this->where('display_name', 'ilike', "%{$search}%");
    }

    public function isSystem(): self
    {
        return $this->where('is_system', true);
    }

    public function isCustom(): self
    {
        return $this->where('is_system', false);
    }

    public function orderBy(?string $column, ?string $direction): self
    {
        if (! $column) {
            return $this->orderBy($column, $direction);
        }

        return $this;
    }
}
