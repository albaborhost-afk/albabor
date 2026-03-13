<?php

namespace App\Console\Commands;

use App\Models\ListingMedia;
use App\Services\ListingImageWatermark;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WatermarkExistingImages extends Command
{
    protected $signature = 'watermark:apply-existing
                            {--listing= : Process a specific listing ID only}
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Apply AlBabor watermark to all existing listing images';

    public function handle(ListingImageWatermark $watermark): int
    {
        $disk = config('filesystems.listing_disk', 'public');
        $dryRun = $this->option('dry-run');

        $query = ListingMedia::query();

        if ($listingId = $this->option('listing')) {
            $query->where('listing_id', $listingId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No listing images found.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Processing {$total} images...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $query->orderBy('id')->chunk(50, function ($mediaItems) use ($watermark, $disk, $dryRun, &$processed, &$skipped, &$errors, $bar) {
            foreach ($mediaItems as $media) {
                // Process main image
                if ($media->path && Storage::disk($disk)->exists($media->path)) {
                    if (!$dryRun) {
                        $result = $watermark->applyToStoredImage($disk, $media->path, 85);
                        if ($result) {
                            Storage::disk($disk)->put($media->path, $result);
                        } else {
                            $this->newLine();
                            $this->error("  Failed: {$media->path}");
                            $errors++;
                            $bar->advance();
                            continue;
                        }
                    }
                } else {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Process thumbnail
                if ($media->thumbnail_path && Storage::disk($disk)->exists($media->thumbnail_path)) {
                    if (!$dryRun) {
                        $result = $watermark->applyToStoredImage($disk, $media->thumbnail_path, 75);
                        if ($result) {
                            Storage::disk($disk)->put($media->thumbnail_path, $result);
                        }
                    }
                }

                $processed++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Done! Processed: {$processed}, Skipped: {$skipped}, Errors: {$errors}");

        if ($dryRun) {
            $this->warn('This was a dry run. No files were modified.');
        }

        return self::SUCCESS;
    }
}
