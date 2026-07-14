<?php

namespace Database\Seeders;

use App\Models\Laptop;
use App\Models\LaptopBrand;
use Illuminate\Database\Seeder;

class LaptopSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Acer', 'Aspire 3 A315-24P', 'AMD Ryzen 5 7520U'], ['Acer', 'Aspire 5 A515-57', 'Intel Core i5-1235U'], ['Acer', 'Swift 3 SF314-512', 'Intel Core i5-1235U'], ['Acer', 'Nitro 5 AN515-58', 'Intel Core i5-12500H'], ['Acer', 'Predator Helios 300 PH315-55', 'Intel Core i7-12700H'],
            ['ASUS', 'VivoBook 14 X1404', 'Intel Core i3-1215U'], ['ASUS', 'VivoBook 15 OLED K513', 'Intel Core i5-1135G7'], ['ASUS', 'ZenBook 14 UX3402', 'Intel Core i7-1260P'], ['ASUS', 'ROG Strix G15 G513', 'AMD Ryzen 7 5800H'], ['ASUS', 'TUF Gaming F15 FX507', 'Intel Core i7-12700H'],
            ['Lenovo', 'IdeaPad 1 14ALC7', 'AMD Ryzen 3 5300U'], ['Lenovo', 'IdeaPad Slim 3 14IAH8', 'Intel Core i5-12450H'], ['Lenovo', 'ThinkPad E14 Gen 4', 'Intel Core i5-1235U'], ['Lenovo', 'ThinkPad T14 Gen 3', 'AMD Ryzen 7 PRO 6850U'], ['Lenovo', 'Yoga Slim 7 Pro', 'AMD Ryzen 7 5800H'],
            ['HP', '14s-dq', 'Intel Core i3-1115G4'], ['HP', '15s-fq', 'Intel Core i5-1135G7'], ['HP', 'Pavilion 14-ec', 'AMD Ryzen 5 5500U'], ['HP', 'Envy x360 13', 'AMD Ryzen 5 5625U'], ['HP', 'Victus 16-d', 'Intel Core i7-12700H'],
            ['Dell', 'Inspiron 14 5420', 'Intel Core i5-1235U'], ['Dell', 'Inspiron 15 3520', 'Intel Core i3-1215U'], ['Dell', 'Vostro 14 5410', 'Intel Core i5-11320H'], ['Dell', 'Latitude 5420', 'Intel Core i5-1145G7'], ['Dell', 'G15 5520', 'Intel Core i7-12700H'],
            ['Apple', 'MacBook Air M1', 'Apple M1 8-Core'], ['Apple', 'MacBook Pro 13 M2', 'Apple M2 8-Core'], ['Apple', 'MacBook Air M2', 'Apple M2 8-Core'], ['Apple', 'MacBook Air M3', 'Apple M3 8-Core'], ['Apple', 'MacBook Pro 14 M3 Pro', 'Apple M3 8-Core'],
            ['MSI', 'Modern 14 B11M', 'Intel Core i5-1155G7'], ['MSI', 'Modern 15 B12M', 'Intel Core i7-1255U'], ['MSI', 'Katana 15 B12V', 'Intel Core i7-12700H'], ['MSI', 'Pulse 15 B13V', 'Intel Core i7-13700H'], ['MSI', 'Stealth 16 Studio', 'Intel Core i7-13700H'],
            ['Huawei', 'MateBook D15', 'Intel Core i5-1135G7'], ['Huawei', 'MateBook 14', 'AMD Ryzen 5 5500U'], ['Huawei', 'MateBook D16', 'Intel Core i5-12450H'], ['Microsoft', 'Surface Laptop 4', 'Intel Core i5-1135G7'], ['Microsoft', 'Surface Laptop 5', 'Intel Core i5-1235U'],
            ['Samsung', 'Galaxy Book2', 'Intel Core i5-1235U'], ['Samsung', 'Galaxy Book3', 'Intel Core i7-1355U'], ['Razer', 'Blade 14', 'AMD Ryzen 9 6900HX'], ['Razer', 'Blade 15', 'Intel Core i7-12700H'], ['Gigabyte', 'G5 MF', 'Intel Core i5-12500H'],
            ['Gigabyte', 'Aero 14 OLED', 'Intel Core i7-13700H'], ['Framework', 'Laptop 13 Gen 12', 'Intel Core i5-1340P'], ['LG', 'Gram 14Z90Q', 'Intel Core i7-1260P'], ['Toshiba', 'Dynabook Tecra A50', 'Intel Core i5-1135G7'], ['Fujitsu', 'LIFEBOOK U7412', 'Intel Core i5-1235U'],
        ];

        foreach ($data as [$brandName, $modelName, $processorName]) {
            $brand = LaptopBrand::firstOrCreate(['name' => $brandName]);
            $score = str_contains($processorName, 'i3') ? 6000 : (str_contains($processorName, 'Ryzen 3') ? 7000 : 15000);
            Laptop::updateOrCreate(
                ['brand_id' => $brand->id, 'model_name' => $modelName],
                ['processor_name' => $processorName, 'benchmark_score' => $score, 'category' => $score <= 7999 ? 'Rendah' : ($score <= 18000 ? 'Sedang' : 'Tinggi')]
            );
        }
    }
}
