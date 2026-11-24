<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Presentation\MethodCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Slide 25 : chaque méthode du catalogue existe bien dans la version de Laravel installée.
 */
class MethodCatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_every_catalogued_method_exists_in_the_installed_laravel_version(): void
    {
        foreach (MethodCatalogue::all() as $entry) {
            $this->assertNotEmpty($entry['checks'], $entry['name']);
            foreach ($entry['checks'] as [$class, $method]) {
                $this->assertTrue(method_exists($class, $method), "{$entry['name']} : {$class}::{$method}() introuvable");
            }
        }
    }

    public function test_entries_are_complete_and_cover_the_four_crud_categories(): void
    {
        $entries = MethodCatalogue::all();
        $required = ['name', 'kind', 'level', 'description', 'syntax', 'example', 'returns', 'when', 'mistakes', 'practices', 'related', 'doc'];

        foreach ($entries as $entry) {
            foreach ($required as $field) {
                $this->assertNotEmpty($entry[$field], "{$entry['name']} : champ {$field} vide");
            }
            $this->assertContains($entry['kind'], ['model', 'builder', 'state']);
            $this->assertContains($entry['level'], [1, 2, 3]);
            $this->assertStringStartsWith('https://laravel.com/docs/'.MethodCatalogue::docsVersion().'/', $entry['doc']);
        }

        $categories = collect($entries)->pluck('categories')->flatten()->unique()->sort()->values()->all();
        $this->assertSame(['create', 'delete', 'read', 'update'], $categories);
        $this->assertTrue(collect($entries)->contains('tag', 'common'));
        $this->assertTrue(collect($entries)->contains('tag', 'underused'));
    }

    public function test_documentation_links_target_the_installed_major_version(): void
    {
        $this->assertSame(Str::before(app()->version(), '.').'.x', MethodCatalogue::docsVersion());
        $this->get('/')->assertSee(MethodCatalogue::docsUrl('eloquent'), false)->assertSee('Explorer la documentation Eloquent');
    }

    public function test_a_selection_of_catalogued_methods_really_runs(): void
    {
        $this->assertSame('ACC-001', Product::where('sku', 'ACC-001')->sole()->sku);
        $this->assertCount(2, Product::findMany([1, 2, 999]));
        $this->assertTrue(Product::where('stock', 0)->exists());
        $this->assertSame(1299.0, (float) Product::max('price'));

        $created = Product::createOrFirst(['sku' => 'INF-001'], ['name' => 'x', 'category' => 'x', 'price' => 1, 'stock' => 1]);
        $this->assertSame(1, $created->id);

        $affected = Product::upsert(
            [['sku' => 'INF-001', 'name' => 'Pro 14', 'category' => 'Informatique', 'price' => 1199, 'stock' => 8]],
            uniqueBy: ['sku'],
            update: ['price'],
        );
        $this->assertSame(1, $affected);

        // incrementEach() : méthode du Query Builder dans Laravel 12
        $this->assertSame(1, DB::table('products')->where('id', 3)->incrementEach(['stock' => 5]));
        $this->assertSame(37, Product::find(3)->stock);

        $this->assertSame(1, Product::forceDestroy([12]));
        $this->assertDatabaseMissing('products', ['id' => 12]);
    }
}
