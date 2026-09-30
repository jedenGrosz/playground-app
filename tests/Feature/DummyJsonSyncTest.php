<?php

namespace Tests\Feature;

use App\Models\ExternalApiSnapshot;
use App\Models\ExternalUser;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\DummyJsonSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DummyJsonSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_entities_items_and_complete_api_snapshots(): void
    {
        Http::fake([
            '*/products*' => Http::response([
                'products' => [[
                    'id' => 10,
                    'title' => 'Test product',
                    'description' => 'Full description',
                    'category' => 'testing',
                    'price' => 12.5,
                    'discountPercentage' => 10,
                    'stock' => 7,
                    'customField' => 'kept in raw payload',
                ]],
                'total' => 1,
                'skip' => 0,
                'limit' => 1,
            ]),
            '*/users*' => Http::response([
                'users' => [[
                    'id' => 20,
                    'firstName' => 'Anna',
                    'lastName' => 'Tester',
                    'email' => 'anna@example.test',
                    'username' => 'annat',
                ]],
                'total' => 1,
                'skip' => 0,
                'limit' => 1,
            ]),
            '*/carts*' => Http::response([
                'carts' => [[
                    'id' => 30,
                    'userId' => 20,
                    'total' => 25,
                    'discountedTotal' => 22.5,
                    'totalProducts' => 1,
                    'totalQuantity' => 2,
                    'products' => [[
                        'id' => 10,
                        'title' => 'Test product',
                        'price' => 12.5,
                        'quantity' => 2,
                        'total' => 25,
                        'discountPercentage' => 10,
                        'discountedTotal' => 22.5,
                    ]],
                ]],
                'total' => 1,
                'skip' => 0,
                'limit' => 1,
            ]),
        ]);

        $counts = app(DummyJsonSyncService::class)->sync();

        $this->assertSame(['products' => 1, 'customers' => 1, 'orders' => 1, 'items' => 1], $counts);
        $this->assertSame('kept in raw payload', Product::firstOrFail()->raw_payload['customField']);
        $this->assertSame(20, ExternalUser::firstOrFail()->dummyjson_id);
        $this->assertSame(20, Order::firstOrFail()->dummyjson_user_id);
        $this->assertSame(10, OrderItem::firstOrFail()->dummyjson_product_id);
        $this->assertCount(3, ExternalApiSnapshot::all());
        $this->assertSame(1, ExternalApiSnapshot::where('resource', 'products')->firstOrFail()->response['total']);
    }
}
