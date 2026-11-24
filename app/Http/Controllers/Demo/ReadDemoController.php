<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * READ — récupérer un, plusieurs ou une page de produits.
 */
class ReadDemoController extends Controller
{
    public function all(): JsonResponse
    {
        $products = Product::all();   // Collection de TOUS les produits

        return response()->json($products);
    }

    public function find(string $id): JsonResponse
    {
        $product = Product::find($id);   // Product ou null

        return response()->json(['product' => $product]);
    }

    public function findOrFail(string $id): JsonResponse
    {
        // Absent ? Laravel répond automatiquement 404
        $product = Product::findOrFail($id);

        return response()->json($product);
    }

    public function filter(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'category' => ['required', 'string'],
            'max_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        $products = Product::where('category', $filters['category'])
            ->where('price', '<=', $filters['max_price'])
            ->where('stock', '>=', $filters['min_stock'])
            ->orderBy('price')
            ->get();                       // exécute la requête

        return response()->json($products);
    }

    public function paginate(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => ['integer', 'min:1', 'max:50'],
            'page' => ['integer', 'min:1'],
        ]);

        // ?page=… est lu automatiquement par Laravel
        $products = Product::orderBy('id')
            ->paginate($request->integer('per_page', 5));

        return response()->json($products);
    }
}
