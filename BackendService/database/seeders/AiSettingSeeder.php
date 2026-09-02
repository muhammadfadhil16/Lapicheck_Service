<?php

namespace Database\Seeders;

use App\Models\AiSetting;
use Illuminate\Database\Seeder;

class AiSettingSeeder extends Seeder
{
    public function run(): void
    {
        AiSetting::firstOrCreate([], ['model' => config('services.gemini.model', 'gemini-3.6-flash')]);
    }
}
