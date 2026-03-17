<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    protected $table = 'lookups';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_system' => 'boolean',
        ];
    }

    protected $hidden = [
        'is_system',
    ];

    public function scopeByNamespace(Builder $query, string $namespace): Builder
    {
        return $query->where('namespace', $namespace);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('display_name', 'ilike', "%{$search}%");
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    public function canDelete(): bool
    {
        return ! $this->is_system;
    }
}
