<?php

namespace App\Http\Controllers;

use App\Models\DataSync;
use App\Models\ExternalUser;
use App\Models\Order;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestSync = DataSync::latest('started_at')->first();

        return Inertia::render('Dashboard', [
            'stats' => [
                'products' => Product::count(),
                'customers' => ExternalUser::count(),
                'orders' => Order::count(),
                'revenue' => (float) Order::sum('discounted_total'),
                'lowStock' => Product::where('stock', '<=', 10)->count(),
            ],
            'recentOrders' => Order::query()
                ->with('externalUser:id,first_name,last_name,email')
                ->latest('dummyjson_id')
                ->limit(6)
                ->get(),
            'categories' => Product::query()
                ->selectRaw('category, count(*) as products_count')
                ->groupBy('category')
                ->orderByDesc('products_count')
                ->limit(6)
                ->get(),
            'lowStockProducts' => Product::query()
                ->where('stock', '<=', 10)
                ->orderBy('stock')
                ->limit(5)
                ->get(['id', 'title', 'category', 'stock', 'thumbnail']),
            'sync' => $latestSync ? [
                'status' => $latestSync->status,
                'counts' => $latestSync->counts,
                'finishedAt' => $latestSync->finished_at?->toIso8601String(),
            ] : null,
        ]);
    }
}
