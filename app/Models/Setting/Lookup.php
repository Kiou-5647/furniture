<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    protected $table = 'lookups';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    protected $hidden = [
        'is_system',
    ];

    public function scopeByNamespace($query, string $namespace)
    {
        return $query->where('namespace', $namespace);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function canDelete(): bool
    {
        return ! $this->is_system;
    }
}
