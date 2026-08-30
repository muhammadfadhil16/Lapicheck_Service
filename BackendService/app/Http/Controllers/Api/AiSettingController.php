<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRelevanceKeyword;
use App\Models\AiSetting;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiSettingController extends Controller
{
    public function status()
    {
        $model = $this->selectedModel();

        return response()->json([
            'available' => $this->isHealthy($model),
            'model' => $model,
        ]);
    }

    public function models()
    {
        $models = $this->availableModels();

        return response()->json([
            'data' => $models,
            'selected' => $this->setting()->model,
        ]);
    }

    public function updateModel(Request $request)
    {
        $validated = $request->validate(['model' => 'required|string|max:120']);
        $model = Str::after($validated['model'], 'models/');
        $models = collect($this->availableModels())->pluck('id');

        if ($models->isNotEmpty() && !$models->contains($model)) {
            return response()->json(['message' => 'Model tidak tersedia untuk API key ini.'], 422);
        }

        $setting = $this->setting();
        $setting->update(['model' => $model]);

        return response()->json(['data' => $setting]);
    }

    public function testConnection(Request $request)
    {
        if (!$this->isAvailable()) {
            return response()->json(['message' => 'AI belum diaktifkan atau API key belum tersedia.'], 422);
        }

        $validated = $request->validate(['model' => 'nullable|string|max:120']);
        $model = Str::after($validated['model'] ?? $this->setting()->model, 'models/');
        $models = collect($this->availableModels())->pluck('id');

        if (!$models->contains($model)) {
            return response()->json(['message' => 'Model tidak tersedia untuk API key ini.'], 422);
        }

        Cache::forget($this->healthCacheKey($model));

        if (!$this->isHealthy($model)) {
            return response()->json(['message' => 'Koneksi gagal. Periksa API key, model, dan kuota Google AI Studio.'], 502);
        }

        return response()->json(['message' => "Koneksi ke {$model} berhasil."]);
    }

    public function keywords()
    {
        return response()->json([
            'data' => AiRelevanceKeyword::orderBy('keyword')->pluck('keyword'),
        ]);
    }

    public function storeKeyword(Request $request)
    {
        $validated = $request->validate(['keyword' => 'required|string|max:80']);
        $keyword = trim(mb_strtolower($validated['keyword']));
        $item = AiRelevanceKeyword::firstOrCreate(['keyword' => $keyword]);

        return response()->json(['data' => $item], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroyKeyword(string $keyword)
    {
        AiRelevanceKeyword::where('keyword', $keyword)->delete();

        return response()->noContent();
    }

    private function setting(): AiSetting
    {
        $setting = AiSetting::firstOrCreate([], ['model' => config('services.gemini.model', 'gemini-2.5-flash')]);
        if (empty($setting->model)) {
            $setting->update(['model' => 'gemini-2.5-flash']);
        }
        return $setting;
    }

    private function selectedModel(): string
    {
        try {
            return $this->setting()->model ?: 'gemini-2.5-flash';
        } catch (\Throwable) {
            return config('services.gemini.model', 'gemini-2.5-flash');
        }
    }

    private function isAvailable(): bool
    {
        return (bool) config('services.gemini.enabled') && filled(config('services.gemini.key'));
    }

    private function isHealthy(string $model): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $cacheKey = $this->healthCacheKey($model);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => 'Balas dengan kata OK.']]]],
                    'generationConfig' => ['maxOutputTokens' => 5],
                ]
            );

            $isSuccessful = $response->successful();

            if ($isSuccessful) {
                Cache::put($cacheKey, true, now()->addMinutes(5));
            } else {
                Cache::put($cacheKey, false, now()->addSeconds(30));
            }

            return $isSuccessful;
        } catch (\Exception $e) {
            Cache::put($cacheKey, false, now()->addSeconds(30));
            return false;
        }
    }

    private function healthCacheKey(string $model): string
    {
        return 'gemini_health_' . sha1($model . '|' . (string) config('services.gemini.key'));
    }

    private function availableModels(): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        return [
            ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash'],
            ['id' => 'gemini-2.0-flash', 'name' => 'Gemini 2.0 Flash'],
            ['id' => 'gemini-3.5-flash', 'name' => 'Gemini 3.5 Flash'],
            ['id' => 'gemini-3.6-flash', 'name' => 'Gemini 3.6 Flash'],
        ];
    }
}
