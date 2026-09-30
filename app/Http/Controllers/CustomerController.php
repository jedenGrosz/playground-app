<?php

namespace App\Http\Controllers;

use App\Models\ExternalUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));

        return Inertia::render('Customers/Index', [
            'customers' => ExternalUser::query()
                ->withCount('orders')
                ->withSum('orders', 'discounted_total')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhere('company->name', 'ilike', "%{$search}%");
                }))
                ->orderBy('last_name')
                ->paginate(18)
                ->withQueryString(),
            'filters' => compact('search'),
        ]);
    }

    public function show(ExternalUser $customer): Response
    {
        $customer->load(['orders' => fn ($query) => $query->orderByDesc('dummyjson_id')]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'stats' => [
                'orders' => $customer->orders->count(),
                'quantity' => $customer->orders->sum('total_quantity'),
                'total' => (float) $customer->orders->sum('discounted_total'),
            ],
        ]);
    }
}
