<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Http\Middleware\InspectDemoRequest;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Outils de la présentation (hors contenu pédagogique) :
 * réinitialisation de la base et lecture des inspections SQL.
 */
class DemoToolsController extends Controller
{
    public function reset(ProductSeeder $seeder): JsonResponse
    {
        DB::transaction(function () use ($seeder) {
            DB::table('products')->delete();
            // Remet le compteur AUTOINCREMENT à zéro (SQLite)
            if (DB::getDriverName() === 'sqlite') {
                DB::table('sqlite_sequence')->where('name', 'products')->delete();
            }
            $seeder->run();
        });

        return response()->json([
            'message' => 'Base de données réinitialisée.',
            'products' => Product::withTrashed()->count(),
            'trashed' => Product::onlyTrashed()->count(),
        ]);
    }

    public function inspection(string $id): JsonResponse
    {
        $payload = Cache::store('file')->get(InspectDemoRequest::CACHE_PREFIX.$id);

        abort_if($payload === null, 404, 'Inspection introuvable ou expirée.');

        return response()->json($payload);
    }
}
