<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Analytics\ViewTrackingService;

class SyncViewsCommand extends Command
{
    protected $signature = 'analytics:sync-views';
    protected $description = 'Sync variant view counts from Redis to Database';

    public function handle(ViewTrackingService $service): int
    {
        $this->info('Syncing views from Redis to Database...');

        $count = $service->syncViewsToDatabase();

        $this->info("Successfully synced views for {$count} variants.");
        return self::SUCCESS;
    }
}
