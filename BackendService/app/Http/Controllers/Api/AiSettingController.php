<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRelevanceKeyword;
use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiSettingController extends Controller
{
    public function status()
    {
        return response()->json([
            'available' => $this->isAvailable(),
            'model' => $this->selectedModel(),
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

        $response = Http::timeout(15)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . config('services.gemini.key'),
            [
                'contents' => [['parts' => [['text' => 'Balas dengan kata OK.']]]],
                'generationConfig' => ['maxOutputTokens' => 5],
            ]
        );

        if ($response->failed()) {
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

        return collect($response->json('models', []))
            ->filter(fn ($model) => in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true))
            ->map(fn ($model) => [
                'id' => Str::after($model['name'] ?? '', 'models/'),
                'name' => $model['displayName'] ?? Str::after($model['name'] ?? '', 'models/'),
            ])
            ->filter(fn ($model) => filled($model['id']))
            ->values()
            ->all();
    }
}
