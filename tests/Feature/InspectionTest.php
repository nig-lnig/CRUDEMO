<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * L'onglet SQL et l'onglet Base de données doivent refléter la réalité.
 */
class InspectionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_business_response_is_not_modified_by_the_instrumentation(): void
    {
        $response = $this->getJson('/api/demo/read/find-or-fail/1');

        $this->assertSame(Product::find(1)->toArray(), $response->json());
        $this->assertNotNull($response->headers->get('X-Demo-Inspection'));
    }

    public function test_captured_sql_matches_the_queries_really_executed(): void
    {
        // Référence : requêtes réellement exécutées par la même opération, capturées par Laravel lui-même
        DB::enableQueryLog();
        Product::find(3);
        $expected = DB::getQueryLog()[0];
        DB::flushQueryLog();
        DB::disableQueryLog();

        $inspection = $this->inspection($this->getJson('/api/demo/read/find/3'));
        $operation = collect($inspection['queries'])->where('phase', 'operation')->values();

        $this->assertCount(1, $operation);
        $this->assertSame($expected['query'], $operation[0]['sql']);
        $this->assertSame(['3'], $operation[0]['bindings']);
        $this->assertIsFloat($operation[0]['time_ms'] + 0.0);
        $this->assertSame(1, $inspection['stats']['operation']['count']);
    }

    public function test_inspection_queries_are_separated_from_the_operation(): void
    {
        $inspection = $this->inspection($this->getJson('/api/demo/read/all'));
        $phases = array_column($inspection['queries'], 'phase');

        $this->assertSame(['inspection', 'operation', 'inspection'], $phases);
        $this->assertSame(2, $inspection['stats']['inspection']['count']);
    }

    public function test_database_diff_marks_inserted_rows(): void
    {
        $response = $this->postJson('/api/demo/create/create', [
            'name' => 'Support écran', 'sku' => 'ACC-5555', 'category' => 'Accessoires', 'price' => 30, 'stock' => 4,
        ]);
        $db = $this->inspection($response)['database'];

        $this->assertSame(['inserted' => 1], $db['summary']);
        $this->assertNull($db['rows'][0]['before']);
        $this->assertSame('ACC-5555', $db['rows'][0]['after']['sku']);
        $this->assertSame(13, $db['table']['after_total']);
    }

    public function test_database_diff_lists_exactly_the_modified_columns(): void
    {
        $db = $this->inspection($this->patchJson('/api/demo/update/increment/6', ['quantity' => 3]))['database'];

        $row = $db['rows'][0];
        $this->assertSame('modified', $row['status']);
        $this->assertSame(['stock', 'updated_at'], $row['changed']);
        $this->assertEquals(0, $row['before']['stock']);
        $this->assertEquals(3, $row['after']['stock']);
    }

    public function test_database_diff_identifies_soft_deletes(): void
    {
        $db = $this->inspection($this->deleteJson('/api/demo/delete/model/4'))['database'];

        $this->assertSame('soft_deleted', $db['rows'][0]['status']);
        $this->assertContains('deleted_at', $db['rows'][0]['changed']);
        $this->assertSame(2, $db['table']['after_trashed']);
    }

    public function test_read_operations_show_only_the_matching_rows(): void
    {
        $db = $this->inspection($this->getJson('/api/demo/read/filter?category=Informatique&max_price=500&min_stock=1'))['database'];

        $this->assertSame('read', $db['mode']);
        $this->assertEqualsCanonicalizing([2, 10], array_column($db['rows'], 'id'));
        $this->assertSame(['matched'], array_values(array_unique(array_column($db['rows'], 'status'))));
    }

    public function test_write_without_effect_shows_unchanged_rows(): void
    {
        $db = $this->inspection($this->patchJson('/api/demo/update/decrement/7', ['quantity' => 50]))['database'];

        $this->assertSame([], $db['summary']);
        $this->assertSame('unchanged', $db['rows'][0]['status']);
        $this->assertSame(7, $db['rows'][0]['id']);
    }

    public function test_unknown_inspection_returns_404(): void
    {
        $this->getJson('/api/demo/inspections/0b4f4a3e-6c39-4d6f-a6b5-1c2d3e4f5a6b')->assertNotFound();
    }
}
