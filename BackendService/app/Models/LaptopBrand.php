<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaptopBrand extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function laptops(): HasMany
    {
        return $this->hasMany(Laptop::class, 'brand_id');
    }
}
