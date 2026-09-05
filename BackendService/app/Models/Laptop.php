<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laptop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'model_name',
        'processor_name',
        'benchmark_score',
        'category',
        'market_price',
        'price_month',
        'price_year',
        'price_updated_at',
    ];

    protected $casts = [
        'benchmark_score'  => 'integer',
        'market_price'     => 'integer',
        'price_month'      => 'integer',
        'price_year'       => 'integer',
        'price_updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Laptop $laptop) {
            if ($laptop->market_price !== null && $laptop->market_price > 0) {
                if (empty($laptop->price_month)) {
                    $laptop->price_month = (int) now()->format('n');
                }
                if (empty($laptop->price_year)) {
                    $laptop->price_year = (int) now()->format('Y');
                }
                if ($laptop->isDirty('market_price') || empty($laptop->price_updated_at)) {
                    $laptop->price_updated_at = now();
                }
            }
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(LaptopBrand::class, 'brand_id');
    }
}
