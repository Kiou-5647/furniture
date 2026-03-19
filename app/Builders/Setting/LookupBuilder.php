<?php

namespace App\Builders\Setting;

use App\Enums\LookupType;
use Illuminate\Database\Eloquent\Builder;

class LookupBuilder extends Builder
{
    public function byNamespace(LookupType $type): self
    {
        return $this->where('namespace', $type->value);
    }

    public function search(string $search): self
    {
        return $this->where('display_name', 'ilike', "%{$search}%");
    }
}
