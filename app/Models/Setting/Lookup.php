<?php

namespace App\Models\Setting;

use App\Builders\Setting\LookupBuilder;
use App\Enums\LookupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static LookupBuilder|Lookup query()
 * @method static LookupBuilder|Lookup byNamespace(LookupType $type)
 * @method static LookupBuilder|Lookup search(string $search)
 */
class Lookup extends Model
{
    use HasFactory;

    protected $table = 'lookups';

    protected function casts(): array
    {
        return [
            'namespace' => LookupType::class,
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function newEloquentBuilder($query): LookupBuilder
    {
        return new LookupBuilder($query);
    }
}
