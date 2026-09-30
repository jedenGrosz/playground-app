<?php

namespace App\Http\Controllers;

use App\Services\DummyJsonSyncService;
use Illuminate\Http\RedirectResponse;

class DataSyncController extends Controller
{
    public function store(DummyJsonSyncService $service): RedirectResponse
    {
        $counts = $service->sync();

        return back()->with('success', sprintf(
            'Zsynchronizowano %d produktów, %d klientów i %d zamówień.',
            $counts['products'],
            $counts['customers'],
            $counts['orders'],
        ));
    }
}
