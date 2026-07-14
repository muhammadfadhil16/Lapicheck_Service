<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laptop extends Model
{
    use SoftDeletes;

    protected $fillable = ['brand_id', 'model_name', 'processor_name', 'benchmark_score', 'category'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(LaptopBrand::class, 'brand_id');
    }
}
