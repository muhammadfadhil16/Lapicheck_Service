<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\External\EvaluatorService;
use App\Models\Assessment;
use App\Models\AiRelevanceKeyword;
use App\Models\Laptop;
use App\Models\AssessmentImage; 
use App\Services\AI\AgentAIService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    public function __construct(
        private EvaluatorService $evaluatorService,
        private AgentAIService $aiService
    ) {}

    public function index(Request $request)
    {
        $query = Assessment::with(['laptop.brand', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('laptop_name', 'like', "%{$search}%")
                  ->orWhere('id', (int) $search);
            });
        }

        if ($request->filled('start_date')) {
            $startUtc = Carbon::parse($request->start_date, 'Asia/Jakarta')
                ->startOfDay()
                ->setTimezone('UTC');
            $query->where('created_at', '>=', $startUtc);
        }

        if ($request->filled('end_date')) {
            $endUtc = Carbon::parse($request->end_date, 'Asia/Jakarta')
                ->endOfDay()
                ->setTimezone('UTC');
            $query->where('created_at', '<=', $endUtc);
        }

        $assessments = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $assessments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'laptop_id'      => 'required|exists:laptops,id',
            'laptop_name'    => 'required|string',
            'images'         => 'nullable|array|max:3',
            'images.*'       => 'image|mimes:jpeg,png,jpg|max:2048',
            'lcd'            => 'required|integer|between:0,100',
            'battery'        => 'required|integer|between:0,100',

            'keyboard'       => 'required|integer|between:0,100',
            'ram'            => 'required|numeric|min:0',
            'processor_input'=> 'nullable|numeric|min:0',
            'market_price'   => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'use_ai'         => 'nullable|boolean',
        ], [
            'customer_name.required'   => 'Nama customer wajib diisi.',
            'customer_name.max'        => 'Nama customer maksimal 255 karakter.',
            'laptop_name.required'     => 'Nama perangkat/model laptop wajib diisi.',
            'lcd.required'             => 'Kondisi LCD wajib ditentukan.',
            'lcd.between'              => 'Skor LCD harus antara 0 sampai 100.',
            'battery.required'         => 'Kesehatan baterai wajib ditentukan.',
            'battery.between'          => 'Kesehatan baterai harus antara 0% sampai 100%.',
            'ram.required'             => 'Kapasitas RAM wajib dipilih.',
            'ram.min'                  => 'Kapasitas RAM tidak boleh negatif.',
            'keyboard.required'        => 'Fungsi keyboard wajib ditentukan.',
            'keyboard.between'         => 'Skor keyboard harus antara 0 sampai 100.',
            'processor_id.exists'      => 'Processor yang dipilih tidak ditemukan dalam database.',
            'processor_name.required_without' => 'Nama processor wajib diisi jika tidak memilih dari daftar.',
            'processor_name.max'       => 'Nama processor maksimal 255 karakter.',
            'processor_input.required_without' => 'Benchmark processor wajib diisi jika input manual.',
            'processor_input.min'      => 'Benchmark processor tidak boleh negatif.',
            'market_price.required'    => 'Harga pasaran wajib diisi.',
            'market_price.min'         => 'Harga pasaran tidak boleh negatif.',
            'images.max'               => 'Maksimal 3 foto yang dapat diunggah.',
            'images.*.image'           => 'File harus berupa gambar.',
            'images.*.mimes'           => 'Format gambar harus JPG, JPEG, atau PNG.',
            'images.*.max'             => 'Ukuran gambar maksimal 2MB per file.',
        ]);

        try {
            $laptop = Laptop::with('brand')->findOrFail($request->laptop_id);

            $processorBenchmark = $request->filled('processor_input') && (float) $request->processor_input > 0
                ? (float) $request->processor_input
                : (float) ($laptop->benchmark_score ?: 0);

            $input = [
                'LCD'              => $request->lcd,
                'KesehatanBaterai' => $request->battery,
                'Processor'        => $processorBenchmark,
                'KondisiKeyboard'  => $request->keyboard,
                'RAM'              => $request->ram,
            ];

            // 2. Panggil Service Evaluator (Fuzzy Engine)
            $evaluationResult = $this->evaluatorService->evaluate($input);
            $score            = $evaluationResult['nilaiKelayakan'];
            $status           = $evaluationResult['statusKelayakan'];

            // 3. Hitung Harga Estimasi (Depresiasi Berbasis Skor)
            $estimatedPrice = (int) floor($request->market_price * ($score / 100));

            // 4. Deteksi deskripsi tidak relevan dengan konteks penilaian laptop
            $descriptionIgnored = false;
            $descriptionForAi = $request->description;
            if (!empty(trim($request->description ?? ''))) {
                $lower = mb_strtolower($request->description);
                $componentKeywords = AiRelevanceKeyword::pluck('keyword');

                if ($componentKeywords->isNotEmpty()) {
                    $found = false;
                    foreach ($componentKeywords as $kw) {
                        if (str_contains($lower, mb_strtolower(trim($kw)))) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $descriptionIgnored = true;
                        $descriptionForAi = null;
                    }
                }
            }

            // 5. Dapatkan Kesimpulan Naratif dari AI Service
            $aiConclusion = null;
            $aiUsed = false;
            try {
                $useAi = $request->boolean('use_ai', false);
                $aiConclusion = $this->aiService->getConclusion(
                    $laptop ? "{$laptop->brand->name} {$laptop->model_name}" : $request->laptop_name,
                    $score,
                    $status,
                    $descriptionForAi,
                    $request->lcd,
                    $request->keyboard,
                    $request->ram,
                    $request->battery,
                    $laptop->processor_name ?? '-',
                    $processorBenchmark,
                    $descriptionIgnored,
                    $useAi
                );
                $aiUsed = $this->aiService->aiUsed;
            } catch (\Exception $e) {
                \Log::error('AI Service error: ' . $e->getMessage());
                $aiConclusion = 'tidak ada catatan tambahan';
                if ($descriptionIgnored) {
                    $aiConclusion .= "\n\nPERINGATAN: Deskripsi yang Anda berikan tidak relevan dengan konteks penilaian laptop dan telah diabaikan. Harap berikan deskripsi terkait kondisi laptop untuk analisis yang lebih akurat.";
                }
            }

            // 5. Simpan Data Evaluasi Utama Terlebih Dahulu
            $assessment = Assessment::create([
                'customer_name'   => $request->customer_name,
                'laptop_name'     => $laptop ? "{$laptop->brand->name} {$laptop->model_name}" : $request->laptop_name,
                'laptop_id'       => $laptop?->id,
                'lcd_input'       => $request->lcd,
                'battery_input'   => $request->battery,
                'processor_input' => $processorBenchmark,
                'keyboard_input'  => $request->keyboard,
                'ram_input'       => $request->ram,
                                'final_score'     => $score,
                'status'          => $status,
                'market_price'    => $request->market_price,
                'estimated_price' => $estimatedPrice,
                'description'     => $request->description,
                'ai_conclusion'   => $aiConclusion,
            ]);

            // 6. Alur Baru: Simpan Berkas Gambar ke Storage dan Insert ke Tabel assessment_images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    // Simpan file ke storage/app/public/evaluations
                    $path = $image->store('evaluations', 'public');
                    
                    // Masukkan ke tabel assessment_images lewat relasi Eloquent
                    $assessment->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            $data = $assessment->load(['laptop.brand', 'images'])->toArray();
            $data['description_ignored'] = $descriptionIgnored;

            // Parse warning marker dari ai_conclusion
            $aiWarning = null;
            if (str_contains($aiConclusion, 'PERINGATAN:')) {
                $parts = explode('PERINGATAN:', $aiConclusion, 2);
                $data['ai_conclusion'] = trim($parts[0]);
                $aiWarning = trim($parts[1]);
            }
            $data['ai_warning'] = $aiWarning;
            $data['ai_used'] = $aiUsed;

            return response()->json([
                'status'  => 'success',
                'message' => 'Penilaian dan gambar berhasil disimpan.',
                'data'    => $data
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error', 
                'message' => 'Gagal memproses penilaian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Sertakan juga relasi 'images' di fungsi detail (show)
        $assessment = Assessment::with(['laptop.brand', 'images'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $assessment
        ]);
    }

    public function destroy($id)
    {
        $assessment = Assessment::with('images')->findOrFail($id);

        $images = $assessment->images ?? collect();
        foreach ($images as $image) {
            try {
                $path = $image->getRawOriginal('image_path');
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete image file: ' . $e->getMessage());
            }
        }

        $assessment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data penilaian beserta file gambar berhasil dihapus.'
        ]);
    }
}