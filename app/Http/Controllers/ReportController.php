<?php

namespace App\Http\Controllers;

use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ReportController extends Controller
{
    public function index(Request $request, SalesReportService $reports): Response
    {
        $filters = $reports->filters($request);

        if ($filters['type'] === 'products') {
            $query = $reports->productsQuery($filters);
            $allRows = $query->get();
            $page = LengthAwarePaginator::resolveCurrentPage();
            $rows = new LengthAwarePaginator(
                $allRows->forPage($page, $filters['per_page'])->values(),
                $allRows->count(),
                $filters['per_page'],
                $page,
                ['path' => $request->url(), 'query' => $request->query()],
            );
            $summary = $reports->productSummary($allRows);
        } else {
            $query = $reports->ordersQuery($filters);
            $rows = $query->paginate($filters['per_page'])->withQueryString();
            $summary = $reports->orderSummary($query);
        }

        return Inertia::render('Reports/Index', [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'countries' => $reports->countries(),
            'categories' => $reports->categories(),
        ]);
    }

    public function download(Request $request, SalesReportService $reports): HttpResponse
    {
        $filters = $reports->filters($request);
        $pdf = $reports->pdf($filters);

        return $pdf->download(sprintf('raport-%s-%s.pdf', $filters['type'], now()->format('Y-m-d-His')));
    }
}
