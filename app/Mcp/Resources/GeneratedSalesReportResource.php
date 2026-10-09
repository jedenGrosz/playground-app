<?php

namespace App\Mcp\Resources;

use App\Services\SalesReportService;
use Illuminate\Support\Facades\Cache;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;
use RuntimeException;

#[Description('A generated sales report PDF. Obtain its URI by calling the generate sales report tool.')]
#[MimeType('application/pdf')]
class GeneratedSalesReportResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('sales-report://{token}');
    }

    public function handle(Request $request, SalesReportService $reports): Response
    {
        $filters = Cache::get(self::cacheKey((string) $request->get('token')));

        if (! is_array($filters)) {
            throw new RuntimeException('The sales report resource has expired or does not exist.');
        }

        return Response::blob($reports->pdf($filters)->output());
    }

    public static function resourceUri(string $token): string
    {
        return "sales-report://{$token}";
    }

    public static function cacheKey(string $token): string
    {
        return "mcp:sales-report:{$token}";
    }
}
