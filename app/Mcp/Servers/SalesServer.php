<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\GeneratedSalesReportResource;
use App\Mcp\Tools\GenerateSalesReportTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Sales Server')]
#[Version('0.0.1')]
#[Instructions('Use this server to generate downloadable sales reports. Reports can be filtered by report type, search phrase, country, category, and price range.')]
class SalesServer extends Server
{
    protected array $tools = [
        GenerateSalesReportTool::class,
    ];

    protected array $resources = [
        GeneratedSalesReportResource::class,
    ];

    protected array $prompts = [
        //
    ];
}
