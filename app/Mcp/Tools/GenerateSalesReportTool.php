<?php

namespace App\Mcp\Tools;

use App\Mcp\Resources\GeneratedSalesReportResource;
use App\Services\SalesReportService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Generate a downloadable PDF sales report using the same filters as the reports screen. The returned link is valid for 15 minutes.')]
#[IsReadOnly]
class GenerateSalesReportTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, SalesReportService $reports): Response
    {
        $filters = $reports->filters($request->all());
        $expiresAt = now()->addMinutes(15);
        $token = (string) Str::uuid();
        Cache::put(GeneratedSalesReportResource::cacheKey($token), $filters, $expiresAt);
        $filename = sprintf('raport-%s-%s.pdf', $filters['type'], now()->format('Y-m-d-His'));

        return Response::resourceLink(
            uri: GeneratedSalesReportResource::resourceUri($token),
            name: $filename,
            mimeType: 'application/pdf',
            title: $filters['type'] === 'products' ? 'Raport sprzedaży produktów' : 'Raport zamówień',
            description: sprintf(
                'Pobierz przefiltrowany raport PDF przez MCP. Zasób jest ważny do %s.',
                $expiresAt->toIso8601String(),
            ),
        );
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()
                ->enum(['orders', 'products'])
                ->default('orders')
                ->description('Typ raportu: orders dla zamówień albo products dla sprzedaży produktów.'),
            'search' => $schema->string()
                ->max(100)
                ->description('Fraza wyszukiwania. Dla zamówień: ID, imię, nazwisko lub e-mail; dla produktów: nazwa produktu.'),
            'country' => $schema->string()
                ->max(100)
                ->description('Dokładna nazwa kraju klienta, np. Poland.'),
            'category' => $schema->string()
                ->max(100)
                ->description('Dokładna kategoria produktu. Ten filtr dotyczy tylko raportu products.'),
            'price_min' => $schema->number()
                ->min(0)
                ->description('Minimalna wartość po rabacie.'),
            'price_max' => $schema->number()
                ->min(0)
                ->description('Maksymalna wartość po rabacie; musi być większa lub równa price_min.'),
            'per_page' => $schema->integer()
                ->enum([10, 25, 50, 100])
                ->default(25)
                ->description('Rozmiar strony używany przez ekran raportów. PDF zawsze zawiera wszystkie pasujące rekordy.'),
        ];
    }
}
