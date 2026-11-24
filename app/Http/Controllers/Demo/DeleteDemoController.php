<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * DELETE — suppression logique (SoftDeletes) et définitive.
 */
class DeleteDemoController extends Controller
{
    public function delete(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);   // on a déjà le modèle

        // SoftDeletes : UPDATE ... SET deleted_at = maintenant
        $product->delete();

        return response()->json([
            'deleted' => true,
            'trashed' => $product->trashed(),
            'product' => $product,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        // On ne fournit que des identifiants
        $count = Product::destroy($data['ids']);   // int

        return response()->json(['deleted_count' => $count]);
    }

    public function trashed(): JsonResponse
    {
        return response()->json([
            'default_count' => Product::count(),
            'with_trashed_count' => Product::withTrashed()->count(),
            'only_trashed' => Product::onlyTrashed()->get(),
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        // On cherche UNIQUEMENT dans la corbeille
        $product = Product::onlyTrashed()->findOrFail($id);

        $product->restore();    // deleted_at = NULL

        return response()->json($product);
    }

    public function forceDelete(Request $request, string $id): Response
    {
        $request->validate(['confirm' => ['accepted']]);

        $product = Product::withTrashed()->findOrFail($id);

        // DELETE FROM products : la ligne disparaît pour de bon
        $product->forceDelete();

        return response()->noContent();   // 204
    }
}
