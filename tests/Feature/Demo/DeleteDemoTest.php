<?php

namespace Tests\Feature\Demo;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteDemoTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_delete_performs_a_soft_delete(): void
    {
        $response = $this->deleteJson('/api/demo/delete/model/4');

        $response->assertOk()->assertJsonPath('deleted', true)->assertJsonPath('trashed', true);
        $this->assertSoftDeleted('products', ['id' => 4]);
        $this->assertDatabaseCount('products', 12);

        $statements = collect($this->inspection($response)['queries'])->where('phase', 'operation')->pluck('statement')->all();
        $this->assertSame(['SELECT', 'UPDATE'], $statements, 'Avec SoftDeletes, delete() exécute un UPDATE.');
    }

    public function test_delete_returns_404_when_already_deleted_or_missing(): void
    {
        $this->deleteJson('/api/demo/delete/model/12')->assertNotFound();
        $this->deleteJson('/api/demo/delete/model/999')->assertNotFound();
    }

    public function test_destroy_deletes_several_products_by_id(): void
    {
        $this->deleteJson('/api/demo/delete/destroy', ['ids' => [2, 3]])
            ->assertOk()
            ->assertExactJson(['deleted_count' => 2]);

        $this->assertSoftDeleted('products', ['id' => 2]);
        $this->assertSoftDeleted('products', ['id' => 3]);
    }

    public function test_destroy_ignores_unknown_ids_and_validates_input(): void
    {
        $this->deleteJson('/api/demo/delete/destroy', ['ids' => [999]])->assertOk()->assertExactJson(['deleted_count' => 0]);
        $this->deleteJson('/api/demo/delete/destroy', ['ids' => []])->assertUnprocessable();
    }

    public function test_trashed_compares_default_with_trashed_and_only_trashed(): void
    {
        $response = $this->getJson('/api/demo/delete/trashed');

        $response->assertOk()
            ->assertJsonPath('default_count', 11)
            ->assertJsonPath('with_trashed_count', 12)
            ->assertJsonCount(1, 'only_trashed')
            ->assertJsonPath('only_trashed.0.id', 12);

        $sql = collect($this->inspection($response)['queries'])->where('phase', 'operation')->pluck('sql')->values();
        $this->assertStringContainsString('"deleted_at" is null', $sql[0]);
        $this->assertStringNotContainsString('deleted_at', $sql[1]);
        $this->assertStringContainsString('"deleted_at" is not null', $sql[2]);
    }

    public function test_restore_brings_back_a_soft_deleted_product(): void
    {
        $response = $this->patchJson('/api/demo/delete/restore/12');

        $response->assertOk()->assertJsonPath('deleted_at', null);
        $this->assertNotSoftDeleted('products', ['id' => 12]);
        $this->assertSame('restored', $this->inspection($response)['database']['rows'][0]['status']);
    }

    public function test_restore_only_targets_trashed_products(): void
    {
        $this->patchJson('/api/demo/delete/restore/1')->assertNotFound();
        $this->patchJson('/api/demo/delete/restore/999')->assertNotFound();
    }

    public function test_force_delete_requires_an_explicit_confirmation(): void
    {
        $this->deleteJson('/api/demo/delete/force/12', ['confirm' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['confirm']);

        $this->assertDatabaseHas('products', ['id' => 12]);
    }

    public function test_force_delete_permanently_removes_the_row(): void
    {
        $id = Product::create(['name' => 'Produit jetable', 'sku' => 'TMP-1', 'category' => 'Démo', 'price' => 1, 'stock' => 1])->id;

        $response = $this->deleteJson("/api/demo/delete/force/{$id}", ['confirm' => true]);

        $response->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $id]);
        $this->assertSame('force_deleted', $this->inspection($response)['database']['rows'][0]['status']);
    }

    public function test_force_delete_works_on_trashed_products_and_returns_404_otherwise(): void
    {
        $this->deleteJson('/api/demo/delete/force/12', ['confirm' => true])->assertNoContent();
        $this->deleteJson('/api/demo/delete/force/999', ['confirm' => true])->assertNotFound();
    }
}
