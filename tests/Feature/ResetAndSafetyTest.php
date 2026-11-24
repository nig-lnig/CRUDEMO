<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetAndSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_reset_restores_the_original_dataset(): void
    {
        $this->postJson('/api/demo/create/create', ['name' => 'X', 'sku' => 'X-1', 'category' => 'Démo', 'price' => 1, 'stock' => 1]);
        $this->deleteJson('/api/demo/delete/destroy', ['ids' => [1, 2, 3]]);
        $this->patchJson('/api/demo/update/increment/6', ['quantity' => 50]);
        $this->deleteJson('/api/demo/delete/force/12', ['confirm' => true]);

        $this->postJson('/api/demo/reset')
            ->assertOk()
            ->assertJsonPath('products', 12)
            ->assertJsonPath('trashed', 1);

        foreach (ProductSeeder::PRODUCTS as $expected) {
            $product = Product::withTrashed()->findOrFail($expected['id']);
            $this->assertSame($expected['sku'], $product->sku);
            $this->assertSame($expected['stock'], $product->stock);
        }
        $this->assertTrue(Product::withTrashed()->find(ProductSeeder::TRASHED_ID)->trashed());
        $this->assertDatabaseMissing('products', ['sku' => 'X-1']);
    }

    public function test_ids_restart_after_reset_so_demonstrations_stay_predictable(): void
    {
        $this->postJson('/api/demo/create/create', ['name' => 'X', 'sku' => 'X-2', 'category' => 'Démo', 'price' => 1, 'stock' => 1])
            ->assertJsonPath('id', 13);

        $this->postJson('/api/demo/reset')->assertOk();

        $this->postJson('/api/demo/create/create', ['name' => 'Y', 'sku' => 'Y-2', 'category' => 'Démo', 'price' => 1, 'stock' => 1])
            ->assertJsonPath('id', 13);
    }

    public function test_demo_endpoints_are_unavailable_outside_local_environment(): void
    {
        $this->app['env'] = 'production';

        $this->getJson('/api/demo/read/all')->assertNotFound();
        $this->postJson('/api/demo/reset')->assertNotFound();
        $this->assertDatabaseCount('products', 12);
    }

    public function test_presentation_page_does_not_modify_the_database(): void
    {
        $before = Product::withTrashed()->orderBy('id')->get()->toArray();

        $this->get('/')->assertOk();

        $this->assertSame($before, Product::withTrashed()->orderBy('id')->get()->toArray());
    }
}
