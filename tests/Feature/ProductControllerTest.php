<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * L'API REST « classique » présentée sur la slide 3.
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_index_paginates(): void
    {
        $this->getJson('/api/products?per_page=4')->assertOk()->assertJsonPath('total', 11)->assertJsonCount(4, 'data');
    }

    public function test_store_show_update_destroy(): void
    {
        $id = $this->postJson('/api/products', ['name' => 'Tapis', 'sku' => 'ACC-8000', 'category' => 'Accessoires', 'price' => 9.5, 'stock' => 3])
            ->assertCreated()->json('id');

        $this->getJson("/api/products/{$id}")->assertOk()->assertJsonPath('sku', 'ACC-8000');
        $this->patchJson("/api/products/{$id}", ['stock' => 8])->assertOk()->assertJsonPath('stock', 8);
        $this->deleteJson("/api/products/{$id}")->assertNoContent();
        $this->getJson("/api/products/{$id}")->assertNotFound();
        $this->assertSoftDeleted('products', ['id' => $id]);
    }

    public function test_store_validates_and_unique_sku_is_enforced(): void
    {
        $this->postJson('/api/products', ['name' => 'Doublon', 'sku' => 'INF-001', 'category' => 'Informatique', 'price' => 1, 'stock' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);
    }
}
