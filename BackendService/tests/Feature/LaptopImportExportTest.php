<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\LaptopBrand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LaptopImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_template(): void
    {
        $response = $this->get('/api/laptops/template?format=xlsx');
        $response->assertStatus(200);
        $this->assertTrue(str_contains((string) $response->headers->get('content-disposition'), 'template_import_laptop_'));

        $csvResponse = $this->get('/api/laptops/template?format=csv');
        $csvResponse->assertStatus(200);
        $this->assertTrue(str_contains((string) $csvResponse->headers->get('content-disposition'), '.csv'));
    }

    public function test_can_export_laptops(): void
    {
        $brand = LaptopBrand::create(['name' => 'Dell']);
        Laptop::create([
            'brand_id'         => $brand->id,
            'model_name'       => 'XPS 13',
            'processor_name'   => 'Intel Core i7-1260P',
            'benchmark_score'  => 14500,
            'category'         => 'Sedang',
            'market_price'     => 15000000,
            'price_month'      => 9,
            'price_year'       => 2026,
            'price_updated_at' => now(),
        ]);

        $response = $this->get('/api/laptops/export?format=xlsx');
        $response->assertStatus(200);
        $this->assertTrue(str_contains((string) $response->headers->get('content-disposition'), 'export_data_laptop_'));

        $csvResponse = $this->get('/api/laptops/export?format=csv');
        $csvResponse->assertStatus(200);
        $this->assertTrue(str_contains((string) $csvResponse->headers->get('content-disposition'), '.csv'));
    }

    public function test_can_import_laptops_from_csv(): void
    {
        $csvContent = "Brand,Model Laptop,Processor,Skor Benchmark,Harga Pasaran (Rp),Bulan (1-12),Tahun\n" .
            "HP,Pavilion 14,Intel Core i5-1135G7,9800,7500000,8,2026\n" .
            "Lenovo,Legion 5,AMD Ryzen 7 5800H,21500,13500000,9,2026\n";

        $file = UploadedFile::fake()->createWithContent('laptops.csv', $csvContent);

        $response = $this->postJson('/api/laptops/import', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'imported_count' => 2,
                    'updated_count'  => 0,
                    'errors'         => [],
                ],
            ]);

        $this->assertDatabaseHas('laptop_brands', ['name' => 'HP']);
        $this->assertDatabaseHas('laptop_brands', ['name' => 'Lenovo']);
        $this->assertDatabaseHas('laptops', [
            'model_name'      => 'Pavilion 14',
            'category'        => 'Sedang',
            'market_price'    => 7500000,
            'price_month'     => 8,
            'price_year'      => 2026,
        ]);
        $this->assertDatabaseHas('laptops', [
            'model_name'      => 'Legion 5',
            'category'        => 'Tinggi',
            'market_price'    => 13500000,
        ]);
    }

    public function test_can_create_laptop_with_market_price_and_auto_month_year(): void
    {
        $response = $this->postJson('/api/laptops', [
            'brand_name'      => 'Apple',
            'model_name'      => 'MacBook Air M1',
            'processor_name'  => 'Apple M1',
            'benchmark_score' => 14800,
            'market_price'    => 11000000,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('laptops', [
            'model_name'   => 'MacBook Air M1',
            'market_price' => 11000000,
            'price_month'  => (int) now()->format('n'),
            'price_year'   => (int) now()->format('Y'),
        ]);
    }
}
