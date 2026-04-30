<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageCleanCommand extends Command
{
    protected $signature = 'storage:clean';
    protected $description = 'Clear all images and files in public and media-library storage for fresh migration';

    public function handle(): void
    {
        $publicPath = storage_path('app/public');
        $mediaLibraryPath = storage_path('media-library');

        $this->info('Cleaning storage...');

        if (File::exists($publicPath)) {
            File::cleanDirectory($publicPath);
            $this->line("✅ Cleared contents of: {$publicPath}");
        } else {
            $this->warn("⚠️ Public storage path not found: {$publicPath}");
        }

        if (File::exists($mediaLibraryPath)) {
            File::deleteDirectory($mediaLibraryPath);
            $this->line("✅ Deleted directory: {$mediaLibraryPath}");
        } else {
            $this->warn("⚠️ Media library path not found: {$mediaLibraryPath}");
        }

        $this->info('Storage cleaning completed successfully!');
    }
}
