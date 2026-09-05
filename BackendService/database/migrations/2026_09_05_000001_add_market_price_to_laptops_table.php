<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->unsignedBigInteger('market_price')->nullable()->default(0)->after('category');
            $table->unsignedTinyInteger('price_month')->nullable()->after('market_price');
            $table->unsignedSmallInteger('price_year')->nullable()->after('price_month');
            $table->timestamp('price_updated_at')->nullable()->after('price_year');
        });
    }

    public function down(): void
    {
        Schema::table('laptops', function (Blueprint $table) {
            $table->dropColumn(['market_price', 'price_month', 'price_year', 'price_updated_at']);
        });
    }
};
