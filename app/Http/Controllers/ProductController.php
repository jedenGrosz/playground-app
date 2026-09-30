<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));
        $category = trim((string) $request->query('category'));

        return Inertia::render('Products/Index', [
            'products' => Product::query()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('title', 'ilike', "%{$search}%")
                        ->orWhere('brand', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%");
                }))
                ->when($category, fn ($query) => $query->where('category', $category))
                ->orderBy('title')
                ->paginate(18)
                ->withQueryString(),
            'categories' => Product::query()->distinct()->orderBy('category')->pluck('category'),
            'filters' => compact('search', 'category'),
        ]);
    }

    public function show(Product $product): Response
    {
        $sales = $product->orderItems()
            ->selectRaw('COUNT(DISTINCT order_id) AS orders_count')
            ->selectRaw('COALESCE(SUM(quantity), 0) AS quantity')
            ->selectRaw('COALESCE(SUM(discounted_total), 0) AS revenue')
            ->first();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'sales' => [
                'orders' => (int) $sales->orders_count,
                'quantity' => (int) $sales->quantity,
                'revenue' => (float) $sales->revenue,
            ],
        ]);
    }
}
