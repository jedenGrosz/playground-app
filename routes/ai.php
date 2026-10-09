<?php

use App\Mcp\Servers\SalesServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('sales', SalesServer::class);
