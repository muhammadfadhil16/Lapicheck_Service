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
        return AiSetting::firstOrCreate([], ['model' => config('services.gemini.model', 'gemini-2.5-flash')]);
    }

    private function selectedModel(): string
    {
        try {
            return $this->setting()->model;
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

        return Cache::remember($this->healthCacheKey($model), now()->addMinutes(5), function () use ($model) {
            $response = Http::timeout(10)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => 'Balas dengan kata OK.']]]],
                    'generationConfig' => ['maxOutputTokens' => 5],
                ]
            );

            return $response->successful();
        });
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

        $response = Http::timeout(10)->get('https://generativelanguage.googleapis.com/v1beta/models', [
            'key' => config('services.gemini.key'),
        ]);

        if ($response->failed()) {
            return [];
        }

        $models = collect($response->json('models', []))
            ->filter(fn ($model) => in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true))
            ->map(fn ($model) => [
                'id'   => Str::after($model['name'] ?? '', 'models/'),
                'name' => $model['displayName'] ?? Str::after($model['name'] ?? '', 'models/'),
            ])
            ->filter(fn ($model) => filled($model['id']))
            ->values();

        $this->warmHealthCache($models->pluck('id')->all());

        return $models
            ->filter(fn ($model) => $this->isHealthy($model['id']))
            ->values()
            ->all();
    }

    /**
     * Pre-warm the health cache for all given model IDs using parallel HTTP requests.
     * Only uncached models are checked, so subsequent calls within 5 minutes are instant.
     */
    private function warmHealthCache(array $modelIds): void
    {
        $uncached = array_values(array_filter(
            $modelIds,
            fn ($id) => !Cache::has($this->healthCacheKey($id))
        ));

        if (empty($uncached)) {
            return;
        }

        $key = config('services.gemini.key');

        $responses = Http::pool(function (Pool $pool) use ($uncached, $key) {
            return array_map(
                fn ($id) => $pool->as($id)->timeout(10)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$id}:generateContent?key={$key}",
                    [
                        'contents'         => [['parts' => [['text' => 'Balas dengan kata OK.']]]],
                        'generationConfig' => ['maxOutputTokens' => 5],
                    ]
                ),
                $uncached
            );
        });

        foreach ($uncached as $id) {
            $healthy = isset($responses[$id]) && $responses[$id]->successful();
            Cache::put($this->healthCacheKey($id), $healthy, now()->addMinutes(5));
        }
    }
}
