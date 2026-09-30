<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DummyJsonSyncService;
use Illuminate\Console\Command;

class SyncDummyJson extends Command
{
    protected $signature = 'dummyjson:sync {--force : Synchronize even when local data already exists}';

    protected $description = 'Import products, customers and carts from DummyJSON';

    public function handle(DummyJsonSyncService $service): int
    {
        if (! $this->option('force') && Product::query()->exists()) {
            $this->components->info('DummyJSON data already exists. Use --force to refresh it.');

            return self::SUCCESS;
        }

        $this->components->info('Synchronizing DummyJSON data...');
        $counts = $service->sync();

        $this->components->info(sprintf(
            'Imported %d products, %d customers, %d orders and %d order items.',
            $counts['products'],
            $counts['customers'],
            $counts['orders'],
            $counts['items'],
        ));

        return self::SUCCESS;
    }
}
