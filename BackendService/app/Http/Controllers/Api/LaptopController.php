<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laptop;
use App\Models\LaptopBrand;
use App\Services\LaptopImportExportService;
use Illuminate\Http\Request;

class LaptopController extends Controller
{
    public function brands(Request $request)
    {
        return response()->json(
            LaptopBrand::withCount('laptops')
                ->orderBy('name')
                ->paginate(min($request->integer('per_page', 10), 100), ['id', 'name'])
        );
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $name = trim($data['name']);
        $brand = LaptopBrand::withTrashed()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if ($brand && !$brand->trashed()) {
            return response()->json([
                'message' => 'Brand sudah terdaftar.',
                'errors' => ['name' => ['Brand sudah terdaftar.']],
            ], 422);
        }

        if ($brand) {
            $brand->restore();
            $brand->update(['name' => $name]);
        } else {
            $brand = LaptopBrand::create(['name' => $name]);
        }

        return response()->json(['status' => 'success', 'data' => $brand->loadCount('laptops')], 201);
    }

    public function updateBrand(Request $request, LaptopBrand $brand)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:laptop_brands,name,' . $brand->id]);
        $brand->update(['name' => trim($data['name'])]);
        return response()->json(['status' => 'success', 'data' => $brand->loadCount('laptops')]);
    }

    public function destroyBrand(LaptopBrand $brand)
    {
        if ($brand->laptops()->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Brand masih memiliki data laptop aktif.'], 422);
        }

        $brand->delete();
        return response()->json(['status' => 'success', 'message' => 'Brand berhasil diarsipkan.']);
    }

    public function index(Request $request)
    {
        $query = Laptop::with('brand:id,name')
            ->orderByRaw('price_year DESC, price_month DESC, updated_at DESC, model_name ASC');

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(model_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(processor_name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('brand', function ($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        return response()->json(
            $query->paginate(
                min($request->integer('per_page', 10), 100),
                [
                    'id',
                    'brand_id',
                    'model_name',
                    'processor_name',
                    'benchmark_score',
                    'category',
                    'market_price',
                    'price_month',
                    'price_year',
                    'price_updated_at',
                ]
            )
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_name'      => 'required|string|max:255',
            'model_name'      => 'required|string|max:255',
            'processor_name'  => 'required|string|max:255',
            'benchmark_score' => 'required|integer|min:0',
            'market_price'    => 'nullable|integer|min:0',
            'price_month'     => 'nullable|integer|between:1,12',
            'price_year'      => 'nullable|integer|between:2000,2100',
        ]);

        $brand = LaptopBrand::firstOrCreate(['name' => trim($data['brand_name'])]);
        $score = (int) $data['benchmark_score'];
        $category = $score <= 7999 ? 'Rendah' : ($score <= 18000 ? 'Sedang' : 'Tinggi');

        $marketPrice = isset($data['market_price']) ? (int) $data['market_price'] : 0;
        $priceMonth = $data['price_month'] ?? ($marketPrice > 0 ? (int) now()->format('n') : null);
        $priceYear = $data['price_year'] ?? ($marketPrice > 0 ? (int) now()->format('Y') : null);

        $laptop = Laptop::create([
            'brand_id'         => $brand->id,
            'model_name'       => trim($data['model_name']),
            'processor_name'   => trim($data['processor_name']),
            'benchmark_score'  => $score,
            'category'         => $category,
            'market_price'     => $marketPrice,
            'price_month'      => $priceMonth,
            'price_year'       => $priceYear,
            'price_updated_at' => $marketPrice > 0 ? now() : null,
        ]);

        return response()->json(['status' => 'success', 'data' => $laptop->load('brand')], 201);
    }

    public function update(Request $request, Laptop $laptop)
    {
        $data = $request->validate([
            'brand_id'        => 'required|exists:laptop_brands,id',
            'model_name'      => 'required|string|max:255',
            'processor_name'  => 'required|string|max:255',
            'benchmark_score' => 'required|integer|min:0',
            'market_price'    => 'nullable|integer|min:0',
            'price_month'     => 'nullable|integer|between:1,12',
            'price_year'      => 'nullable|integer|between:2000,2100',
        ]);

        $score = (int) $data['benchmark_score'];
        $marketPrice = isset($data['market_price']) ? (int) $data['market_price'] : 0;
        $priceMonth = $data['price_month'] ?? ($marketPrice > 0 ? ($laptop->price_month ?: (int) now()->format('n')) : null);
        $priceYear = $data['price_year'] ?? ($marketPrice > 0 ? ($laptop->price_year ?: (int) now()->format('Y')) : null);

        $laptop->update([
            'brand_id'         => $data['brand_id'],
            'model_name'       => trim($data['model_name']),
            'processor_name'   => trim($data['processor_name']),
            'benchmark_score'  => $score,
            'category'         => $score <= 7999 ? 'Rendah' : ($score <= 18000 ? 'Sedang' : 'Tinggi'),
            'market_price'     => $marketPrice,
            'price_month'      => $priceMonth,
            'price_year'       => $priceYear,
            'price_updated_at' => $marketPrice > 0 ? now() : null,
        ]);

        return response()->json(['status' => 'success', 'data' => $laptop->load('brand')]);
    }

    public function destroy(Laptop $laptop)
    {
        $laptop->delete();

        return response()->json(['status' => 'success', 'message' => 'Data laptop berhasil dihapus.']);
    }

    public function import(Request $request, LaptopImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.required' => 'File spreadsheet wajib diunggah.',
            'file.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $result = $service->import($request->file('file'));

        return response()->json([
            'status'  => 'success',
            'message' => "Import selesai: {$result['imported_count']} data baru ditambahkan, {$result['updated_count']} data diperbarui.",
            'data'    => $result,
        ]);
    }

    public function template(Request $request, LaptopImportExportService $service)
    {
        $format = $request->query('format') === 'csv' ? 'csv' : 'xlsx';
        return $service->generateTemplate($format);
    }

    public function export(Request $request, LaptopImportExportService $service)
    {
        $format = $request->query('format') === 'csv' ? 'csv' : 'xlsx';

        $query = Laptop::with('brand:id,name')
            ->orderByRaw('price_year DESC, price_month DESC, updated_at DESC, model_name ASC');

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(model_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(processor_name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('brand', function ($q) use ($search) {
                      $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $laptops = $query->get();

        return $service->export($laptops, $format);
    }
}
