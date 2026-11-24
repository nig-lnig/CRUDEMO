<?php

namespace Tests\Feature\Demo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadDemoTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_all_returns_every_product_except_soft_deleted_ones(): void
    {
        $this->getJson('/api/demo/read/all')
            ->assertOk()
            ->assertJsonCount(11)
            ->assertJsonMissing(['sku' => 'ACC-004']);
    }

    public function test_find_returns_the_product(): void
    {
        $this->getJson('/api/demo/read/find/3')
            ->assertOk()
            ->assertJsonPath('product.sku', 'ACC-001');
    }

    public function test_find_returns_null_with_200_for_a_missing_product(): void
    {
        $this->getJson('/api/demo/read/find/999')
            ->assertOk()
            ->assertExactJson(['product' => null]);
    }

    public function test_find_ignores_soft_deleted_products(): void
    {
        $this->getJson('/api/demo/read/find/12')->assertOk()->assertExactJson(['product' => null]);
    }

    public function test_find_or_fail_returns_the_product(): void
    {
        $this->getJson('/api/demo/read/find-or-fail/3')->assertOk()->assertJsonPath('id', 3);
    }

    public function test_find_or_fail_returns_a_real_404(): void
    {
        $this->getJson('/api/demo/read/find-or-fail/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'No query results for model [App\\Models\\Product] 999');
    }

    public function test_filter_applies_every_where_condition(): void
    {
        $response = $this->getJson('/api/demo/read/filter?category=Informatique&max_price=500&min_stock=1');

        $response->assertOk()->assertJsonCount(2);
        $this->assertSame(['INF-003', 'INF-002'], array_column($response->json(), 'sku'));
    }

    public function test_filter_can_return_an_empty_collection(): void
    {
        $this->getJson('/api/demo/read/filter?category=Informatique&max_price=1&min_stock=0')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_filter_validates_parameters(): void
    {
        $this->getJson('/api/demo/read/filter?category=Informatique&max_price=abc&min_stock=1')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['max_price']);
    }

    public function test_paginate_returns_real_pagination_metadata(): void
    {
        $this->getJson('/api/demo/read/paginate?per_page=5&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 5)
            ->assertJsonPath('total', 11)
            ->assertJsonPath('last_page', 3)
            ->assertJsonPath('data.0.id', 6)
            ->assertJsonCount(5, 'data');
    }

    public function test_paginate_limits_per_page(): void
    {
        $this->getJson('/api/demo/read/paginate?per_page=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }
}
