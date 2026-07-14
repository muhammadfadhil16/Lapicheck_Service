<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laptop_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('laptops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('laptop_brands')->cascadeOnDelete();
            $table->string('model_name');
            $table->string('processor_name');
            $table->integer('benchmark_score');
            $table->enum('category', ['Rendah', 'Sedang', 'Tinggi']);
            $table->timestamps();
            $table->unique(['brand_id', 'model_name']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('laptop_id')->nullable()->after('laptop_name')->constrained('laptops')->nullOnDelete();
        });

        Schema::dropIfExists('processors');
    }

    public function down(): void
    {
        Schema::table('assessments', fn (Blueprint $table) => $table->dropConstrainedForeignId('laptop_id'));
        Schema::dropIfExists('laptops');
        Schema::dropIfExists('laptop_brands');
    }
};
