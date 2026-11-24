<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Contrôleur REST « classique » : le CRUD tel qu'on l'écrit dans un vrai projet.
 * Route::apiResource('products', ProductController::class);
 */
class ProductController extends Controller
{
    // GET /api/products
    public function index(Request $request): JsonResponse
    {
        $request->validate(['per_page' => ['integer', 'min:1', 'max:50']]);

        $products = Product::orderBy('id')
            ->paginate($request->integer('per_page', 10));

        return response()->json($products);
    }

    // POST /api/products
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    // GET /api/products/{id}
    public function show(string $id): JsonResponse
    {
        return response()->json(Product::findOrFail($id));
    }

    // PATCH /api/products/{id}
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        return response()->json($product);
    }

    // DELETE /api/products/{id}
    public function destroy(string $id): Response
    {
        Product::findOrFail($id)->delete();

        return response()->noContent();
    }
}
