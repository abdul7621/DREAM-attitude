<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportJob;
use App\Services\ShopifyImporter;
use App\Services\WooImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $jobs = ImportJob::query()->orderByDesc('id')->limit(20)->get();

        return view('admin.import.index', compact('jobs'));
    }

    public function show(ImportJob $importJob): View
    {
        return view('admin.import.show', compact('importJob'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'source' => 'required|in:shopify,woo',
            'type'   => 'required|in:products,customers,orders',
            'file'   => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $file   = $request->file('file');
        $source = $request->input('source');
        $type   = $request->input('type');

        $path = $file->store('imports', 'local');
        $hash = md5_file(Storage::disk('local')->path($path));

        $job = ImportJob::query()->create([
            'source'   => $source.'_'.$type,
            'filename' => $path,
            'status'   => 'uploaded',
            'stats'    => ['type' => $type, 'file_hash' => $hash, 'dry_run' => true],
        ]);

        return redirect()->route('admin.import.preview', $job);
    }

    public function preview(ImportJob $importJob): RedirectResponse|View
    {
        if ($importJob->status === 'uploaded') {
            [$source, $type] = explode('_', $importJob->source, 2);

            $importer = match ($source) {
                'shopify' => app(ShopifyImporter::class),
                'woo'     => app(WooImporter::class),
                default   => null,
            };

            if ($importer) {
                $preview = match ($type) {
                    'products'  => $importer->dryRun(Storage::disk('local')->path($importJob->filename)),
                    'customers' => method_exists($importer, 'dryRunCustomers')
                                   ? $importer->dryRunCustomers(Storage::disk('local')->path($importJob->filename))
                                   : ['customers' => 0, 'dry_run' => true, 'error' => 'Not supported for this platform'],
                    'orders'    => method_exists($importer, 'dryRunOrders')
                                   ? $importer->dryRunOrders(Storage::disk('local')->path($importJob->filename))
                                   : ['orders' => 0, 'dry_run' => true, 'error' => 'Not supported for this platform'],
                    default     => [],
                };

                $importJob->update([
                    'status' => 'previewed',
                    'stats'  => array_merge((array) $importJob->stats, $preview),
                ]);
            }
        }

        return view('admin.import.preview', compact('importJob'));
    }

    public function confirmPage(ImportJob $importJob): RedirectResponse|View
    {
        if (!in_array($importJob->status, ['previewed', 'processing'])) {
            return redirect()->route('admin.import.index')->with('error', 'Preview this import first.');
        }

        $stats = $importJob->stats ?? [];
        return view('admin.import.confirm', compact('importJob', 'stats'));
    }

    public function chunk(Request $request, ImportJob $importJob): \Illuminate\Http\JsonResponse
    {
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $offset = (int) $request->input('offset', 0);
        $limit  = (int) $request->input('limit', 5);

        [$source, $type] = explode('_', $importJob->source, 2);

        $importer = match ($source) {
            'shopify' => app(ShopifyImporter::class),
            'woo'     => app(WooImporter::class),
            default   => null,
        };

        if (!$importer || $type !== 'products') {
            return response()->json(['error' => 'Unsupported import type for chunked processing'], 400);
        }

        try {
            $filePath = Storage::disk('local')->path($importJob->filename);
            $result = $importer->importChunk($filePath, $offset, $limit);

            // Merge running totals into job stats
            $stats = (array) $importJob->stats;
            $stats['products']  = ($stats['products_done'] ?? 0) + ($result['products'] ?? 0);
            $stats['products_done'] = $stats['products'];
            $stats['variants']  = ($stats['variants_done'] ?? 0) + ($result['variants'] ?? 0);
            $stats['variants_done'] = $stats['variants'];
            $stats['images']    = ($stats['images_done'] ?? 0) + ($result['images'] ?? 0);
            $stats['images_done'] = $stats['images'];

            // Accumulate errors
            $existingErrors = $stats['errors'] ?? [];
            if (!is_array($existingErrors)) $existingErrors = [];
            $stats['errors'] = array_merge($existingErrors, $result['errors'] ?? []);

            $done = $result['done'] ?? false;
            $stats['status'] = $done ? 'completed' : 'processing';

            $importJob->update([
                'status' => $done ? 'completed' : 'processing',
                'stats'  => $stats,
            ]);

            return response()->json([
                'done'      => $done,
                'offset'    => $offset + $result['processed_parents'],
                'processed' => $stats['products_done'],
                'total'     => $result['total_parents'],
                'products'  => $stats['products_done'],
                'variants'  => $stats['variants_done'],
                'images'    => $stats['images_done'],
                'errors'    => count($stats['errors']),
                'chunk_errors' => $result['errors'] ?? [],
            ]);
        } catch (\Throwable $e) {
            $importJob->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportErrors(ImportJob $importJob)
    {
        $errors = $importJob->stats['errors'] ?? [];
        if (empty($errors)) {
            return back()->with('error', 'No errors to export.');
        }

        $filename = 'import_errors_job_' . $importJob->id . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($errors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Error Message', 'Raw Data (JSON)']);

            foreach ($errors as $error) {
                if (is_string($error)) {
                    fputcsv($file, [$error, '']);
                } else {
                    $msg = $error['message'] ?? 'Unknown Error';
                    $raw = $error['raw'] ?? [];
                    fputcsv($file, [$msg, json_encode($raw)]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
