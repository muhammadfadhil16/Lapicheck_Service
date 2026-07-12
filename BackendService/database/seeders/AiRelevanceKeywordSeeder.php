<?php

namespace Database\Seeders;

use App\Models\AiRelevanceKeyword;
use Illuminate\Database\Seeder;

class AiRelevanceKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            'keyboard', 'baterai', 'battery', 'lcd', 'layar',
            'ram', 'processor', 'prosesor', 'cpu', 'hardisk', 'hdd', 'ssd', 'charger',
            'bodi', 'casing', 'engsel', 'port', 'usb', 'hdmi', 'audio', 'fan', 'kipas',
            'touchpad', 'trackpad', 'webcam', 'kamera', 'speaker', 'pengeras suara',
            'mikrofon', 'wifi', 'bluetooth', 'motherboard', 'mainboard', 'gpu', 'vga',
            'lecet', 'baret', 'penyok', 'retak', 'pecah', 'rusak', 'mati', 'berisik',
            'mulus', 'normal', 'berfungsi', 'menyala', 'panas', 'overheat', 'lambat',
            'upgrade', 'servis', 'service', 'perbaiki', 'ganti', 'longgar', 'hilang',
        ];

        foreach ($keywords as $keyword) {
            AiRelevanceKeyword::firstOrCreate(['keyword' => $keyword]);
        }
    }
}
