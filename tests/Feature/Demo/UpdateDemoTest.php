<?php

namespace Tests\Feature\Demo;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDemoTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_save_updates_only_the_dirty_attributes(): void
    {
        $response = $this->patchJson('/api/demo/update/save/2', ['price' => 429.90, 'stock' => 20]);

        $response->assertOk()
            ->assertJsonPath('original.price', '449.90')
            ->assertJsonPath('original.stock', 15)
            ->assertJsonPath('changes.stock', 20);
        $this->assertDatabaseHas('products', ['id' => 2, 'price' => 429.90, 'stock' => 20]);

        $update = collect($this->inspection($response)['queries'])->firstWhere('statement', 'UPDATE');
        $this->assertStringContainsString('"price" = ?', $update['sql']);
        $this->assertStringNotContainsString('"name"', $update['sql']);
    }

    public function test_save_without_changes_runs_no_update(): void
    {
        $response = $this->patchJson('/api/demo/update/save/2', ['price' => 449.90, 'stock' => 15]);

        $response->assertOk()->assertJsonPath('changes', []);
        $statements = collect($this->inspection($response)['queries'])->where('phase', 'operation')->pluck('statement');
        $this->assertNotContains('UPDATE', $statements);
    }

    public function test_save_returns_404_and_422(): void
    {
        $this->patchJson('/api/demo/update/save/999', ['price' => 1, 'stock' => 1])->assertNotFound();
        $this->patchJson('/api/demo/update/save/2', ['price' => -1, 'stock' => 'vingt'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'stock']);
    }

    public function test_update_applies_validated_attributes(): void
    {
        $this->patchJson('/api/demo/update/direct/5', ['name' => 'Casque 2026', 'price' => 249])
            ->assertOk()
            ->assertJsonPath('name', 'Casque 2026')
            ->assertJsonPath('price', '249.00');

        $this->assertDatabaseHas('products', ['id' => 5, 'name' => 'Casque 2026', 'stock' => 12]);
    }

    public function test_update_rejects_a_sku_used_by_another_product(): void
    {
        $this->patchJson('/api/demo/update/direct/5', ['sku' => 'INF-001'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);

        // Son propre SKU reste autorisé
        $this->patchJson('/api/demo/update/direct/5', ['sku' => 'AUD-001'])->assertOk();
    }

    public function test_bulk_update_returns_the_affected_rows_and_ignores_trashed_products(): void
    {
        $this->patchJson('/api/demo/update/bulk', ['category' => 'Accessoires', 'max_stock' => 10, 'is_active' => false])
            ->assertOk()
            ->assertExactJson(['affected_rows' => 2]);

        $this->assertEqualsCanonicalizing([4, 7], Product::where('is_active', false)->pluck('id')->all());
        $this->assertTrue((bool) Product::withTrashed()->find(12)->is_active);
    }

    public function test_bulk_update_requires_a_filter(): void
    {
        $this->patchJson('/api/demo/update/bulk', ['max_stock' => 10, 'is_active' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category']);

        $this->assertSame(0, Product::where('is_active', false)->count());
    }

    public function test_increment_is_atomic(): void
    {
        $response = $this->patchJson('/api/demo/update/increment/6', ['quantity' => 20]);

        $response->assertOk()->assertExactJson(['stock_before' => 0, 'stock_after' => 20]);
        $this->assertDatabaseHas('products', ['id' => 6, 'stock' => 20]);

        $update = collect($this->inspection($response)['queries'])->firstWhere('statement', 'UPDATE');
        $this->assertStringContainsString('"stock" = "stock" + 20', $update['sql']);
    }

    public function test_decrement_succeeds_when_stock_is_sufficient(): void
    {
        $this->patchJson('/api/demo/update/decrement/7', ['quantity' => 2])
            ->assertOk()
            ->assertExactJson(['stock_before' => 5, 'stock_after' => 3]);
    }

    public function test_decrement_refuses_insufficient_stock_with_a_conditional_update(): void
    {
        $response = $this->patchJson('/api/demo/update/decrement/7', ['quantity' => 50]);

        $response->assertStatus(409)->assertJsonPath('message', 'Stock insuffisant.');
        $this->assertDatabaseHas('products', ['id' => 7, 'stock' => 5]);

        // La condition est DANS la requête UPDATE (atomique), pas seulement en PHP
        $update = collect($this->inspection($response)['queries'])->firstWhere('statement', 'UPDATE');
        $this->assertStringContainsString('"stock" >= ?', $update['sql']);
    }

    public function test_decrement_returns_404_for_a_missing_product(): void
    {
        $this->patchJson('/api/demo/update/decrement/999', ['quantity' => 2])->assertNotFound();
    }
}
