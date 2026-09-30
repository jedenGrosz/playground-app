<?php

namespace App\Services;

use App\Models\ExternalUser;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * @return array{type: string, search: string, country: string, category: string, price_min: float|null, price_max: float|null, per_page: int}
     */
    public function filters(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:orders,products'],
            'search' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        return [
            'type' => $validated['type'] ?? 'orders',
            'search' => trim($validated['search'] ?? ''),
            'country' => trim($validated['country'] ?? ''),
            'category' => trim($validated['category'] ?? ''),
            'price_min' => isset($validated['price_min']) ? (float) $validated['price_min'] : null,
            'price_max' => isset($validated['price_max']) ? (float) $validated['price_max'] : null,
            'per_page' => (int) ($validated['per_page'] ?? 25),
        ];
    }

    /** @return Collection<int, string> */
    public function countries(): Collection
    {
        return ExternalUser::query()
            ->pluck('address')
            ->map(fn (?array $address) => $address['country'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    /** @return Collection<int, string> */
    public function categories(): Collection
    {
        return Product::query()->distinct()->orderBy('category')->pluck('category');
    }

    /** @param array<string, mixed> $filters */
    public function ordersQuery(array $filters): EloquentBuilder
    {
        return Order::query()
            ->with('externalUser:id,first_name,last_name,email,address')
            ->when($filters['search'], fn (EloquentBuilder $query, string $search) => $query->where(function (EloquentBuilder $query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('dummyjson_id', (int) $search);
                }

                $query->orWhereHas('externalUser', fn (EloquentBuilder $query) => $query
                    ->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%"));
            }))
            ->when($filters['country'], fn (EloquentBuilder $query, string $country) => $query
                ->whereHas('externalUser', fn (EloquentBuilder $query) => $query->where('address->country', $country)))
            ->when($filters['price_min'] !== null, fn (EloquentBuilder $query) => $query->where('discounted_total', '>=', $filters['price_min']))
            ->when($filters['price_max'] !== null, fn (EloquentBuilder $query) => $query->where('discounted_total', '<=', $filters['price_max']))
            ->orderByDesc('dummyjson_id');
    }

    /** @param array<string, mixed> $filters */
    public function productsQuery(array $filters): QueryBuilder
    {
        return DB::table('order_items')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('external_users', 'external_users.id', '=', 'orders.external_user_id')
            ->selectRaw('COALESCE(products.id, 0) AS id')
            ->selectRaw('order_items.dummyjson_product_id')
            ->selectRaw('COALESCE(products.title, order_items.title) AS title')
            ->selectRaw("COALESCE(products.category, 'brak-kategorii') AS category")
            ->selectRaw('SUM(order_items.quantity) AS total_quantity')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) AS orders_count')
            ->selectRaw('SUM(order_items.total) AS total')
            ->selectRaw('SUM(order_items.discounted_total) AS discounted_total')
            ->selectRaw('AVG(order_items.price) AS average_price')
            ->when($filters['search'], fn (QueryBuilder $query, string $search) => $query
                ->where(function (QueryBuilder $query) use ($search) {
                    $query->where('products.title', 'ilike', "%{$search}%")
                        ->orWhere('order_items.title', 'ilike', "%{$search}%");
                }))
            ->when($filters['country'], fn (QueryBuilder $query, string $country) => $query->whereIn(
                'orders.external_user_id',
                ExternalUser::query()->where('address->country', $country)->select('id'),
            ))
            ->when($filters['category'], fn (QueryBuilder $query, string $category) => $query->where('products.category', $category))
            ->groupBy(
                'products.id',
                'products.title',
                'products.category',
                'order_items.dummyjson_product_id',
                'order_items.title',
            )
            ->when($filters['price_min'] !== null, fn (QueryBuilder $query) => $query
                ->havingRaw('SUM(CAST(order_items.discounted_total AS NUMERIC)) >= CAST(? AS NUMERIC)', [$filters['price_min']]))
            ->when($filters['price_max'] !== null, fn (QueryBuilder $query) => $query
                ->havingRaw('SUM(CAST(order_items.discounted_total AS NUMERIC)) <= CAST(? AS NUMERIC)', [$filters['price_max']]))
            ->orderByDesc('discounted_total');
    }

    /**
     * @return array{records: int, quantity: int, total: float, discounted_total: float}
     */
    public function orderSummary(EloquentBuilder $query): array
    {
        return [
            'records' => (clone $query)->count(),
            'quantity' => (int) (clone $query)->sum('total_quantity'),
            'total' => (float) (clone $query)->sum('total'),
            'discounted_total' => (float) (clone $query)->sum('discounted_total'),
        ];
    }

    /**
     * @return array{records: int, quantity: int, total: float, discounted_total: float}
     */
    public function productSummary(Collection $rows): array
    {
        return [
            'records' => $rows->count(),
            'quantity' => (int) $rows->sum('total_quantity'),
            'total' => (float) $rows->sum('total'),
            'discounted_total' => (float) $rows->sum('discounted_total'),
        ];
    }
}
