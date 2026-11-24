<?php

namespace Tests\Feature\Demo;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateDemoTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function product(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Lampe de bureau LED',
            'sku' => 'MOB-9001',
            'category' => 'Mobilier',
            'price' => 39.90,
            'stock' => 25,
        ], $overrides);
    }

    public function test_save_creates_a_product_with_timestamps(): void
    {
        $response = $this->postJson('/api/demo/create/save', $this->product());

        $response->assertCreated()
            ->assertJsonPath('id', 13)
            ->assertJsonPath('sku', 'MOB-9001')
            ->assertJsonStructure(['id', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('products', ['sku' => 'MOB-9001', 'stock' => 25]);
        $this->assertNotNull(Product::where('sku', 'MOB-9001')->value('created_at'));
    }

    public function test_save_rejects_invalid_data_without_writing(): void
    {
        $this->postJson('/api/demo/create/save', $this->product(['name' => '', 'sku' => 'INF-001', 'price' => -5, 'stock' => 'beaucoup']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'sku', 'price', 'stock']);

        $this->assertDatabaseCount('products', 12);
    }

    public function test_create_uses_mass_assignment_and_ignores_non_fillable_attributes(): void
    {
        $response = $this->postJson('/api/demo/create/create', ['id' => 500, ...$this->product(['sku' => 'ACC-9001'])]);

        $response->assertCreated()->assertJsonPath('sku', 'ACC-9001');
        $this->assertNotSame(500, $response->json('id'));
        $this->assertDatabaseMissing('products', ['id' => 500]);
    }

    public function test_insert_adds_several_rows_in_one_query_without_timestamps(): void
    {
        $response = $this->postJson('/api/demo/create/insert', ['products' => [
            $this->product(['sku' => 'CAB-1-A']),
            $this->product(['sku' => 'CAB-1-B']),
            $this->product(['sku' => 'CAB-1-C']),
        ]]);

        $response->assertCreated()->assertExactJson(['inserted' => true, 'count' => 3]);
        $this->assertDatabaseCount('products', 15);
        $this->assertNull(Product::where('sku', 'CAB-1-A')->value('created_at'));

        $inserts = collect($this->inspection($response)['queries'])
            ->where('phase', 'operation')->where('statement', 'INSERT');
        $this->assertCount(1, $inserts, 'insert() doit exécuter une seule requête INSERT.');
    }

    public function test_insert_rejects_duplicate_skus_in_the_batch(): void
    {
        $this->postJson('/api/demo/create/insert', ['products' => [
            $this->product(['sku' => 'DUP-1']),
            $this->product(['sku' => 'DUP-1']),
        ]])->assertUnprocessable();

        $this->assertDatabaseCount('products', 12);
    }

    public function test_first_or_create_creates_then_returns_the_existing_product(): void
    {
        $payload = $this->product(['sku' => 'ACC-HUB-1']);

        $this->postJson('/api/demo/create/first-or-create', $payload)->assertCreated();
        $second = $this->postJson('/api/demo/create/first-or-create', [...$payload, 'name' => 'Autre nom']);

        $second->assertOk()->assertJsonPath('name', 'Lampe de bureau LED');
        $this->assertSame(1, Product::where('sku', 'ACC-HUB-1')->count());
    }

    public function test_first_or_create_does_not_modify_an_existing_product(): void
    {
        $this->postJson('/api/demo/create/first-or-create', $this->product(['sku' => 'INF-001', 'name' => 'Nom ignoré', 'price' => 1]))
            ->assertOk()
            ->assertJsonPath('name', 'Ordinateur portable Pro 14')
            ->assertJsonPath('price', '1299.00');
    }

    public function test_update_or_create_updates_an_existing_product(): void
    {
        $this->postJson('/api/demo/create/update-or-create', $this->product(['sku' => 'INF-002', 'name' => 'Écran 27 pouces 4K', 'category' => 'Informatique', 'price' => 399, 'stock' => 20]))
            ->assertOk()
            ->assertJsonPath('price', '399.00');

        $this->assertDatabaseHas('products', ['sku' => 'INF-002', 'price' => 399, 'stock' => 20]);
        $this->assertDatabaseCount('products', 12);
    }

    public function test_update_or_create_creates_a_missing_product(): void
    {
        $this->postJson('/api/demo/create/update-or-create', $this->product(['sku' => 'INF-7777']))
            ->assertCreated();

        $this->assertDatabaseHas('products', ['sku' => 'INF-7777']);
    }

    public function test_update_or_create_validates_input(): void
    {
        $this->postJson('/api/demo/create/update-or-create', ['sku' => 'INF-002', 'name' => '', 'price' => -1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'category', 'stock']);
    }
}
