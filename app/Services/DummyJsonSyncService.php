<?php

namespace App\Services;

use App\Models\DataSync;
use App\Models\ExternalApiSnapshot;
use App\Models\ExternalUser;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class DummyJsonSyncService
{
    /**
     * @return array{products: int, customers: int, orders: int, items: int}
     */
    public function sync(): array
    {
        $sync = DataSync::create([
            'provider' => 'dummyjson',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $baseUrl = rtrim((string) config('services.dummyjson.base_url'), '/');
            $payloads = [];

            foreach (['products', 'users', 'carts'] as $resource) {
                $response = Http::acceptJson()
                    ->timeout(30)
                    ->retry(2, 300)
                    ->get("{$baseUrl}/{$resource}", ['limit' => 0])
                    ->throw();

                $payloads[$resource] = $response->json();
            }

            $counts = DB::transaction(function () use ($payloads, $baseUrl): array {
                $syncedAt = now();

                foreach ($payloads as $resource => $payload) {
                    ExternalApiSnapshot::create([
                        'provider' => 'dummyjson',
                        'resource' => $resource,
                        'endpoint' => "{$baseUrl}/{$resource}?limit=0",
                        'response' => $payload,
                        'fetched_at' => $syncedAt,
                    ]);
                }

                foreach ($payloads['users']['users'] as $user) {
                    ExternalUser::updateOrCreate(
                        ['dummyjson_id' => $user['id']],
                        [
                            'first_name' => $user['firstName'],
                            'last_name' => $user['lastName'],
                            'email' => $user['email'],
                            'username' => $user['username'] ?? null,
                            'phone' => $user['phone'] ?? null,
                            'gender' => $user['gender'] ?? null,
                            'age' => $user['age'] ?? null,
                            'image' => $user['image'] ?? null,
                            'company' => $user['company'] ?? null,
                            'address' => $user['address'] ?? null,
                            'raw_payload' => $user,
                            'synced_at' => $syncedAt,
                        ],
                    );
                }

                foreach ($payloads['products']['products'] as $product) {
                    Product::updateOrCreate(
                        ['dummyjson_id' => $product['id']],
                        [
                            'title' => $product['title'],
                            'description' => $product['description'] ?? null,
                            'category' => $product['category'],
                            'brand' => $product['brand'] ?? null,
                            'sku' => $product['sku'] ?? null,
                            'price' => $product['price'],
                            'discount_percentage' => $product['discountPercentage'] ?? 0,
                            'rating' => $product['rating'] ?? null,
                            'stock' => $product['stock'] ?? 0,
                            'availability_status' => $product['availabilityStatus'] ?? null,
                            'thumbnail' => $product['thumbnail'] ?? null,
                            'images' => $product['images'] ?? [],
                            'raw_payload' => $product,
                            'synced_at' => $syncedAt,
                        ],
                    );
                }

                $userIds = ExternalUser::pluck('id', 'dummyjson_id');
                $productIds = Product::pluck('id', 'dummyjson_id');
                $itemCount = 0;

                foreach ($payloads['carts']['carts'] as $cart) {
                    $order = Order::updateOrCreate(
                        ['dummyjson_id' => $cart['id']],
                        [
                            'external_user_id' => $userIds->get($cart['userId']),
                            'dummyjson_user_id' => $cart['userId'],
                            'total' => $cart['total'],
                            'discounted_total' => $cart['discountedTotal'],
                            'total_products' => $cart['totalProducts'],
                            'total_quantity' => $cart['totalQuantity'],
                            'status' => 'imported',
                            'raw_payload' => $cart,
                            'synced_at' => $syncedAt,
                        ],
                    );

                    $order->items()->delete();

                    foreach ($cart['products'] as $item) {
                        $order->items()->create([
                            'product_id' => $productIds->get($item['id']),
                            'dummyjson_product_id' => $item['id'],
                            'title' => $item['title'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'total' => $item['total'],
                            'discount_percentage' => $item['discountPercentage'] ?? 0,
                            'discounted_total' => $item['discountedTotal'],
                            'thumbnail' => $item['thumbnail'] ?? null,
                            'raw_payload' => $item,
                        ]);
                        $itemCount++;
                    }
                }

                return [
                    'products' => count($payloads['products']['products']),
                    'customers' => count($payloads['users']['users']),
                    'orders' => count($payloads['carts']['carts']),
                    'items' => $itemCount,
                ];
            });

            $sync->update([
                'status' => 'completed',
                'counts' => $counts,
                'finished_at' => now(),
            ]);

            return $counts;
        } catch (Throwable $exception) {
            $sync->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}
