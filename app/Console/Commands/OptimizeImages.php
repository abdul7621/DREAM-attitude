<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--chunk=50 : Process N images per batch}';
    protected $description = 'Convert all existing JPG/PNG images to optimized WebP format';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $optimizer = app(ImageOptimizerService::class);
        $chunkSize = (int) $this->option('chunk');

        $allFiles = $disk->allFiles();
        $candidates = array_filter($allFiles, function ($f) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png']);
        });

        $total = count($candidates);
        $this->info("Found {$total} images to process (chunk size: {$chunkSize})");

        if ($total === 0) {
            $this->info('Nothing to optimize.');
            return 0;
        }

        $processed = 0;
        $converted = 0;
        $savedBytes = 0;
        $skipped = 0;

        $chunks = array_chunk($candidates, $chunkSize);

        foreach ($chunks as $batch) {
            foreach ($batch as $file) {
                $processed++;

                // Skip if WebP version already exists
                $webpPath = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.webp';
                if ($disk->exists($webpPath)) {
                    $skipped++;
                    continue;
                }

                $origSize = $disk->size($file);
                $maxWidth = str_contains($file, 'theme') ? ImageOptimizerService::MAX_HERO : ImageOptimizerService::MAX_PRODUCT;

                $newPath = $optimizer->optimize($file, $maxWidth);

                if ($newPath !== $file && $disk->exists($newPath)) {
                    $newSize = $disk->size($newPath);
                    $saved = $origSize - $newSize;
                    $savedBytes += max(0, $saved);
                    $converted++;

                    if ($processed % 10 === 0) {
                        $this->output->write(".");
                    }
                } else {
                    $skipped++;
                }
            }

            // Give server breathing room between chunks
            if (count($chunks) > 1) {
                gc_collect_cycles();
                usleep(100000); // 100ms pause
            }
        }

        $this->newLine();
        $savedMB = round($savedBytes / 1024 / 1024, 2);
        $this->info("Done! Processed: {$processed}, Converted: {$converted}, Skipped: {$skipped}");
        $this->info("Total space saved: {$savedMB} MB");

        return 0;
    }
}
