<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $primaryKey = 'province_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class, 'province_code', 'province_code');
    }
}
