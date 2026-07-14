<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laptop;
use App\Models\LaptopBrand;
use Illuminate\Http\Request;

class LaptopController extends Controller
{
    public function brands()
    {
        return response()->json(['data' => LaptopBrand::withCount('laptops')->orderBy('name')->get(['id', 'name'])]);
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
        $query = Laptop::with('brand:id,name')->orderBy('model_name');
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        return response()->json(['data' => $query->get(['id', 'brand_id', 'model_name', 'processor_name', 'benchmark_score', 'category'])]);
    }

    public function update(Request $request, Laptop $laptop)
    {
        $data = $request->validate([
            'brand_id' => 'required|exists:laptop_brands,id',
            'model_name' => 'required|string|max:255',
            'processor_name' => 'required|string|max:255',
            'benchmark_score' => 'required|integer|min:0',
        ]);

        $score = (int) $data['benchmark_score'];
        $laptop->update([
            'brand_id' => $data['brand_id'],
            'model_name' => trim($data['model_name']),
            'processor_name' => trim($data['processor_name']),
            'benchmark_score' => $score,
            'category' => $score <= 7999 ? 'Rendah' : ($score <= 18000 ? 'Sedang' : 'Tinggi'),
        ]);

        return response()->json(['status' => 'success', 'data' => $laptop->load('brand')]);
    }

    public function destroy(Laptop $laptop)
    {
        $laptop->delete();

        return response()->json(['status' => 'success', 'message' => 'Data laptop berhasil dihapus.']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_name' => 'required|string|max:255',
            'model_name' => 'required|string|max:255',
            'processor_name' => 'required|string|max:255',
            'benchmark_score' => 'required|integer|min:0',
        ]);

        $brand = LaptopBrand::firstOrCreate(['name' => trim($data['brand_name'])]);
        $score = (int) $data['benchmark_score'];
        $category = $score <= 7999 ? 'Rendah' : ($score <= 18000 ? 'Sedang' : 'Tinggi');
        $laptop = Laptop::create([
            'brand_id' => $brand->id,
            'model_name' => trim($data['model_name']),
            'processor_name' => trim($data['processor_name']),
            'benchmark_score' => $score,
            'category' => $category,
        ]);

        return response()->json(['status' => 'success', 'data' => $laptop->load('brand')], 201);
    }
}
