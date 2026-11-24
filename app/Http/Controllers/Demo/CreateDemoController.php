<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsertProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpsertProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * CREATE — les différentes façons d'ajouter des produits.
 */
class CreateDemoController extends Controller
{
    public function save(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = new Product();           // objet en mémoire
        $product->name = $data['name'];
        $product->sku = $data['sku'];
        $product->category = $data['category'];
        $product->price = $data['price'];
        $product->stock = $data['stock'];
        $product->save();                   // INSERT en base

        return response()->json($product, 201);
    }

    public function create(StoreProductRequest $request): JsonResponse
    {
        // Assignation de masse : seuls les champs $fillable passent
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    public function insert(InsertProductsRequest $request): JsonResponse
    {
        $rows = $request->validated('products');

        // Une seule requête SQL, sans modèle ni événement
        // ⚠ created_at / updated_at ne sont PAS remplis
        $inserted = Product::insert($rows);   // bool

        return response()->json([
            'inserted' => $inserted,
            'count' => count($rows),
        ], 201);
    }

    public function firstOrCreate(UpsertProductRequest $request): JsonResponse
    {
        $product = Product::firstOrCreate(
            ['sku' => $request->validated('sku')], // recherche
            $request->safe()->except('sku'),       // si création
        );

        $status = $product->wasRecentlyCreated ? 201 : 200;

        return response()->json($product, $status);
    }

    public function updateOrCreate(UpsertProductRequest $request): JsonResponse
    {
        $product = Product::updateOrCreate(
            ['sku' => $request->validated('sku')], // recherche
            $request->safe()->except('sku'),       // à écrire
        );

        $status = $product->wasRecentlyCreated ? 201 : 200;

        return response()->json($product, $status);
    }
}
