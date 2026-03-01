<?php

namespace App\Console\Commands;

use App\Models\ListingMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseStorage extends Command
{
    protected $signature = 'storage:diagnose';
    protected $description = 'Diagnose listing image storage issues';

    public function handle(): int
    {
        $disk = config('filesystems.listing_disk', 'public');
        $defaultDisk = config('filesystems.default');
        $filesystemDisk = env('FILESYSTEM_DISK', 'not set');

        $this->info("=== Storage Diagnosis ===");
        $this->info("FILESYSTEM_DISK env: {$filesystemDisk}");
        $this->info("Default disk: {$defaultDisk}");
        $this->info("Listing disk: {$disk}");
        $this->info("Listing disk driver: " . config("filesystems.disks.{$disk}.driver", 'unknown'));

        if ($disk === 's3') {
            $this->info("S3 bucket: " . config('filesystems.disks.s3.bucket', 'not set'));
            $this->info("S3 region: " . config('filesystems.disks.s3.region', 'not set'));
            $this->info("S3 key set: " . (config('filesystems.disks.s3.key') ? 'yes' : 'NO'));
        }

        $totalMedia = ListingMedia::count();
        $this->info("\nTotal media records: {$totalMedia}");

        if ($totalMedia === 0) {
            $this->warn("No media records found.");
            return 0;
        }

        $missing = 0;
        $found = 0;
        $storage = Storage::disk($disk);

        foreach (ListingMedia::all() as $media) {
            $exists = false;
            try {
                $exists = $storage->exists($media->path);
            } catch (\Throwable $e) {
                $this->error("Error checking {$media->path}: {$e->getMessage()}");
            }

            if ($exists) {
                $found++;
                $this->line("  [OK] #{$media->id} → {$media->path}");
            } else {
                $missing++;
                $this->error("  [MISSING] #{$media->id} → {$media->path}");
            }
        }

        $this->info("\nResults: {$found} found, {$missing} missing out of {$totalMedia}");

        if ($missing > 0 && $disk === 'public') {
            $this->warn("\nImages are on LOCAL disk. They get DELETED on each deployment!");
            $this->warn("Fix: Attach an S3 bucket in Laravel Cloud dashboard.");
        }

        return 0;
    }
}
