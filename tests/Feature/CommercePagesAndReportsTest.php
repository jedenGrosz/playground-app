<?php

namespace Tests\Feature;

use App\Models\ExternalUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommercePagesAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ExternalUser $customer;

    private Product $product;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = ExternalUser::create([
            'dummyjson_id' => 20,
            'first_name' => 'Anna',
            'last_name' => 'Tester',
            'email' => 'anna@example.test',
            'address' => ['city' => 'Warszawa', 'country' => 'Poland'],
            'company' => ['name' => 'Test SA', 'title' => 'Buyer'],
            'raw_payload' => ['id' => 20, 'custom' => 'full customer payload'],
            'synced_at' => now(),
        ]);
        $this->product = Product::create([
            'dummyjson_id' => 10,
            'title' => 'Test product',
            'description' => 'Full product description',
            'category' => 'testing',
            'price' => 75,
            'discount_percentage' => 10,
            'stock' => 7,
            'images' => [],
            'raw_payload' => ['id' => 10, 'custom' => 'full product payload'],
            'synced_at' => now(),
        ]);
        $this->order = Order::create([
            'dummyjson_id' => 30,
            'external_user_id' => $this->customer->id,
            'dummyjson_user_id' => 20,
            'total' => 150,
            'discounted_total' => 135,
            'total_products' => 1,
            'total_quantity' => 2,
            'status' => 'imported',
            'raw_payload' => ['id' => 30, 'custom' => 'full order payload'],
            'synced_at' => now(),
        ]);
        $this->order->items()->create([
            'product_id' => $this->product->id,
            'dummyjson_product_id' => 10,
            'title' => 'Test product',
            'price' => 75,
            'quantity' => 2,
            'total' => 150,
            'discount_percentage' => 10,
            'discounted_total' => 135,
            'raw_payload' => ['id' => 10, 'quantity' => 2],
        ]);
    }

    public function test_home_redirects_guests_to_login_and_users_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->actingAs($this->user)->get('/')->assertRedirect('/dashboard');
    }

    public function test_authenticated_users_can_open_full_commerce_details(): void
    {
        $this->actingAs($this->user)
            ->get(route('products.show', $this->product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('product.raw_payload.custom', 'full product payload')
                ->where('sales.quantity', 2));

        $this->get(route('orders.show', $this->order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->where('order.raw_payload.custom', 'full order payload')
                ->has('order.items', 1));

        $this->get(route('customers.show', $this->customer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Show')
                ->where('customer.raw_payload.custom', 'full customer payload')
                ->where('stats.total', 135));
    }

    public function test_reports_apply_filters_and_paginate_only_the_preview(): void
    {
        $this->createAdditionalOrders(10);

        $this->actingAs($this->user)
            ->get('/reports?type=orders&country=Poland&price_min=100&price_max=140&per_page=10')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('rows.total', 11)
                ->where('rows.per_page', 10)
                ->has('rows.data', 10)
                ->where('summary.records', 11)
                ->where('summary.quantity', 22)
                ->where('summary.discounted_total', 1485));

        $this->get('/reports?type=products&country=Poland&category=testing&price_min=100')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->where('rows.total', 1)
                ->where('rows.data.0.title', 'Test product')
                ->where('summary.quantity', 22));
    }

    public function test_filtered_report_can_be_downloaded_as_pdf(): void
    {
        $this->createAdditionalOrders(10);

        $response = $this->actingAs($this->user)
            ->get('/reports/download?type=orders&country=Poland&price_min=100&per_page=10');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function createAdditionalOrders(int $count): void
    {
        foreach (range(1, $count) as $offset) {
            $order = Order::create([
                'dummyjson_id' => 30 + $offset,
                'external_user_id' => $this->customer->id,
                'dummyjson_user_id' => 20,
                'total' => 150,
                'discounted_total' => 135,
                'total_products' => 1,
                'total_quantity' => 2,
                'status' => 'imported',
                'raw_payload' => ['id' => 30 + $offset],
                'synced_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $this->product->id,
                'dummyjson_product_id' => 10,
                'title' => 'Test product',
                'price' => 75,
                'quantity' => 2,
                'total' => 150,
                'discount_percentage' => 10,
                'discounted_total' => 135,
                'raw_payload' => ['id' => 10, 'quantity' => 2],
            ]);
        }
    }
}
