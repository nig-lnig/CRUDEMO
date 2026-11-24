<?php

namespace App\Http\Controllers;

use App\Presentation\MethodCatalogue;
use App\Presentation\Slides;
use App\Presentation\SourceCode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Affiche la présentation. Tout le contenu montré (code, routes, schéma)
 * est lu depuis le projet réel au moment de l'affichage.
 */
class PresentationController extends Controller
{
    public function __invoke(): View
    {
        return view('presentation', [
            'slides' => Slides::all(),
            'sections' => Slides::SECTIONS,
            'projectFiles' => $this->projectFiles(),
            'apiRoutes' => $this->apiRoutes(),
            'catalogue' => MethodCatalogue::all(),
            'docsUrl' => MethodCatalogue::docsUrl('eloquent'),
            'docsVersion' => MethodCatalogue::docsVersion(),
        ]);
    }

    /**
     * Fichiers montrés sur la slide 2 (contenu réel).
     */
    private function projectFiles(): array
    {
        $migration = collect(File::files(database_path('migrations')))
            ->first(fn ($file) => str_contains($file->getFilename(), 'create_products_table'));

        $migrationPath = 'database/migrations/'.$migration->getFilename();

        return [
            ['id' => 'routes', 'path' => 'routes/api.php', 'label' => 'api.php', 'folder' => 'routes', 'icon' => 'route', 'role' => 'Associe chaque URL à une méthode de contrôleur.', 'lang' => 'php', 'code' => SourceCode::file('routes/api.php')],
            ['id' => 'product-controller', 'path' => 'app/Http/Controllers/ProductController.php', 'label' => 'ProductController.php', 'folder' => 'app/Http/Controllers', 'icon' => 'file-code', 'role' => 'Le CRUD REST classique (apiResource).', 'lang' => 'php', 'code' => SourceCode::file('app/Http/Controllers/ProductController.php')],
            ['id' => 'create-demo', 'path' => 'app/Http/Controllers/Demo/CreateDemoController.php', 'label' => 'CreateDemoController.php', 'folder' => 'app/Http/Controllers/Demo', 'icon' => 'file-plus', 'role' => 'Une méthode par technique de création.', 'lang' => 'php', 'code' => SourceCode::file('app/Http/Controllers/Demo/CreateDemoController.php')],
            ['id' => 'read-demo', 'path' => 'app/Http/Controllers/Demo/ReadDemoController.php', 'label' => 'ReadDemoController.php', 'folder' => 'app/Http/Controllers/Demo', 'icon' => 'file-search', 'role' => 'Une méthode par technique de lecture.', 'lang' => 'php', 'code' => SourceCode::file('app/Http/Controllers/Demo/ReadDemoController.php')],
            ['id' => 'update-demo', 'path' => 'app/Http/Controllers/Demo/UpdateDemoController.php', 'label' => 'UpdateDemoController.php', 'folder' => 'app/Http/Controllers/Demo', 'icon' => 'file-pen', 'role' => 'Une méthode par technique de modification.', 'lang' => 'php', 'code' => SourceCode::file('app/Http/Controllers/Demo/UpdateDemoController.php')],
            ['id' => 'delete-demo', 'path' => 'app/Http/Controllers/Demo/DeleteDemoController.php', 'label' => 'DeleteDemoController.php', 'folder' => 'app/Http/Controllers/Demo', 'icon' => 'file-x', 'role' => 'Suppression logique, définitive, restauration.', 'lang' => 'php', 'code' => SourceCode::file('app/Http/Controllers/Demo/DeleteDemoController.php')],
            ['id' => 'model', 'path' => 'app/Models/Product.php', 'label' => 'Product.php', 'folder' => 'app/Models', 'icon' => 'box', 'role' => 'Le modèle Eloquent : $fillable, casts, SoftDeletes.', 'lang' => 'php', 'code' => SourceCode::file('app/Models/Product.php')],
            ['id' => 'migration', 'path' => $migrationPath, 'label' => $migration->getFilename(), 'folder' => 'database/migrations', 'icon' => 'table-2', 'role' => 'La structure de la table products.', 'lang' => 'php', 'code' => SourceCode::file($migrationPath)],
            ['id' => 'database', 'path' => 'database/database.sqlite', 'label' => 'database.sqlite', 'folder' => 'database', 'icon' => 'database', 'role' => 'La base SQLite : colonnes réelles de products.', 'lang' => 'sql', 'code' => $this->schemaDescription()],
        ];
    }

    private function schemaDescription(): string
    {
        if (! Schema::hasTable('products')) {
            return '-- Table products absente : lancez php artisan migrate --seed';
        }

        $lines = ['-- Colonnes réelles lues avec Schema::getColumns(\'products\')', ''];

        foreach (Schema::getColumns('products') as $column) {
            $lines[] = sprintf(
                '%-12s %-16s %s%s',
                $column['name'],
                strtoupper($column['type']),
                $column['nullable'] ? 'NULL' : 'NOT NULL',
                $column['default'] !== null ? ' DEFAULT '.$column['default'] : '',
            );
        }

        $lines[] = '';
        foreach (Schema::getIndexes('products') as $index) {
            $lines[] = sprintf('-- index %s (%s)%s', $index['name'], implode(', ', $index['columns']), $index['unique'] ? ' UNIQUE' : '');
        }

        return implode("\n", $lines);
    }

    /**
     * Routes de l'apiResource, lues depuis le routeur (slide 3).
     */
    private function apiRoutes(): array
    {
        $eloquent = [
            'index' => 'paginate()',
            'store' => 'create()',
            'show' => 'findOrFail()',
            'update' => 'update()',
            'destroy' => 'delete()',
        ];
        $crud = ['index' => 'Read', 'store' => 'Create', 'show' => 'Read', 'update' => 'Update', 'destroy' => 'Delete'];

        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/products'))
            ->map(function ($route) use ($eloquent, $crud) {
                $action = $route->getActionMethod();

                return [
                    'method' => collect($route->methods())->reject(fn ($m) => in_array($m, ['HEAD', 'PUT']))->first(),
                    'uri' => '/'.$route->uri(),
                    'action' => 'ProductController@'.$action,
                    'eloquent' => $eloquent[$action] ?? '',
                    'crud' => $crud[$action] ?? '',
                ];
            })
            ->sortBy(fn ($r) => array_search($r['crud'], ['Create', 'Read', 'Update', 'Delete']).$r['uri'])
            ->values()
            ->all();
    }
}
