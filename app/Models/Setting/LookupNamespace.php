<?php

namespace App\Models\Setting;

use App\Builders\Setting\LookupNamespaceBuilder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LookupNamespace extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lookup_namespaces';

    protected function casts(): array
    {
        return [
            'for_variants' => 'boolean',
            'is_filterable' => 'boolean',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function newEloquentBuilder($query): LookupNamespaceBuilder
    {
        return new LookupNamespaceBuilder($query);
    }

    public function lookups(): HasMany
    {
        return $this->hasMany(Lookup::class, 'namespace_id');
    }

    public function activeLookups(): HasMany
    {
        return $this->hasMany(Lookup::class, 'namespace_id')->where('is_active', true);
    }
}
