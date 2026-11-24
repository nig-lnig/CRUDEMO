<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UPDATE — modifier un produit, plusieurs produits ou un compteur.
 */
class UpdateDemoController extends Controller
{
    public function save(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($id);
        $original = $product->only(['price', 'stock']);

        $product->price = $data['price'];
        $product->stock = $data['stock'];
        $product->save();          // modèle existant → UPDATE

        return response()->json([
            'original' => $original,
            'changes' => $product->getChanges(),
        ]);
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // fill() + save() en une seule ligne
        $product->update($request->validated());

        return response()->json($product);
    }

    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string'],   // filtre obligatoire
            'max_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Un seul UPDATE SQL : aucun modèle chargé, aucun événement
        $affected = Product::where('category', $data['category'])
            ->where('stock', '<=', $data['max_stock'])
            ->update(['is_active' => $data['is_active']]);

        return response()->json(['affected_rows' => $affected]);
    }

    public function increment(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);

        $product = Product::findOrFail($id);
        $stockBefore = $product->stock;

        // SQL : SET stock = stock + ? (atomique)
        $product->increment('stock', $data['quantity']);

        return response()->json([
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
        ]);
    }

    public function decrement(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $product = Product::findOrFail($id);

        // Condition + décrément dans la MÊME requête SQL
        $affected = Product::whereKey($product->id)
            ->where('stock', '>=', $data['quantity'])
            ->decrement('stock', $data['quantity']);

        if ($affected === 0) {
            return response()->json(['message' => 'Stock insuffisant.'], 409);
        }

        return response()->json([
            'stock_before' => $product->stock,
            'stock_after' => $product->fresh()->stock,
        ]);
    }
}
