<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Resources\GeneratedSalesReportResource;
use App\Mcp\Servers\SalesServer;
use App\Mcp\Tools\GenerateSalesReportTool;
use App\Services\SalesReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Mcp\Request;
use Tests\TestCase;

class GenerateSalesReportToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_server_registers_the_report_tool(): void
    {
        SalesServer::tools()->assertRegistered(GenerateSalesReportTool::class);
    }

    public function test_tool_returns_a_temporary_mcp_resource_with_filters(): void
    {
        $response = app(GenerateSalesReportTool::class)->handle(
            new Request([
                'type' => 'products',
                'search' => 'Test product',
                'country' => 'Poland',
                'category' => 'testing',
                'price_min' => 100,
                'price_max' => 200,
                'per_page' => 50,
            ]),
            app(SalesReportService::class),
        );

        $content = $response->content()->toArray();
        $token = str($content['uri'])->after('sales-report://')->toString();
        $filters = Cache::get(GeneratedSalesReportResource::cacheKey($token));

        $this->assertSame('resource_link', $content['type']);
        $this->assertSame('application/pdf', $content['mimeType']);
        $this->assertStringStartsWith('raport-products-', $content['name']);
        $this->assertStringStartsWith('sales-report://', $content['uri']);
        $this->assertSame('products', $filters['type']);
        $this->assertSame('Test product', $filters['search']);
        $this->assertSame('Poland', $filters['country']);
        $this->assertSame('testing', $filters['category']);
        $this->assertSame(100.0, $filters['price_min']);
        $this->assertSame(200.0, $filters['price_max']);
        $this->assertSame(50, $filters['per_page']);
    }

    public function test_generated_mcp_resource_contains_a_pdf(): void
    {
        $response = app(GenerateSalesReportTool::class)->handle(
            new Request(['type' => 'orders', 'country' => 'Poland']),
            app(SalesReportService::class),
        );
        $uri = $response->content()->toArray()['uri'];
        $token = str($uri)->after('sales-report://')->toString();

        SalesServer::resource(GeneratedSalesReportResource::class, [
            'token' => $token,
        ])->assertOk();

        $resource = app(GeneratedSalesReportResource::class)->handle(
            new Request(['token' => $token]),
            app(SalesReportService::class),
        );

        $this->assertStringStartsWith('%PDF-', (string) $resource->content());
    }

    public function test_tool_rejects_an_invalid_price_range(): void
    {
        SalesServer::tool(GenerateSalesReportTool::class, [
            'price_min' => 200,
            'price_max' => 100,
        ])->assertHasErrors(['price max']);
    }

    public function test_unknown_mcp_report_resource_is_rejected(): void
    {
        SalesServer::resource(GeneratedSalesReportResource::class, [
            'token' => 'unknown',
        ])->assertHasErrors(['expired or does not exist']);
    }
}
