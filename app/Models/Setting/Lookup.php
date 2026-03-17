<?php

namespace App\Models\Setting;

use App\Builders\Setting\LookupBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static LookupBuilder|Lookup query()
 * @method static LookupBuilder|Lookup byNamespace(string $namespace)
 * @method static LookupBuilder|Lookup search(string $search)
 * @method static LookupBuilder|Lookup isSystem()
 * @method static LookupBuilder|Lookup isCustom()
 */
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

    public function canDelete(): bool
    {
        return ! $this->is_system;
    }

    public function newEloquentBuilder($query): LookupBuilder
    {
        return new LookupBuilder($query);
    }
}
