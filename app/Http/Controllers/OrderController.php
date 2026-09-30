<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));

        return Inertia::render('Orders/Index', [
            'orders' => Order::query()
                ->with(['externalUser:id,first_name,last_name,email,image', 'items:id,order_id,title,quantity,discounted_total'])
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    if (is_numeric($search)) {
                        $query->where('dummyjson_id', (int) $search);
                    }

                    $query->orWhereHas('externalUser', fn ($query) => $query
                        ->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%"));
                }))
                ->orderByDesc('dummyjson_id')
                ->paginate(12)
                ->withQueryString(),
            'filters' => compact('search'),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'externalUser',
            'items.product:id,title,category,brand,thumbnail',
        ]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }
}
