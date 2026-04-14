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

    public function confirm(Request $request, ImportJob $importJob): RedirectResponse
    {
        if ($importJob->status !== 'previewed') {
            return redirect()->route('admin.import.index')->with('error', 'Preview this import first.');
        }

        [$source, $type] = explode('_', $importJob->source, 2);

        $importer = match ($source) {
            'shopify' => app(ShopifyImporter::class),
            'woo'     => app(WooImporter::class),
            default   => null,
        };

        try {
            if ($importer) {
                $filePath = Storage::disk('local')->path($importJob->filename);

                $stats = match ($type) {
                    'products'  => $importer->import($filePath),
                    'customers' => method_exists($importer, 'importCustomers')
                                   ? $importer->importCustomers($filePath)
                                   : throw new \RuntimeException('Customer import not supported for this platform.'),
                    'orders'    => method_exists($importer, 'importOrders')
                                   ? $importer->importOrders($filePath)
                                   : throw new \RuntimeException('Order import not supported for this platform.'),
                    default     => throw new \RuntimeException('Unknown import type.'),
                };

                $importJob->update([
                    'status' => 'completed',
                    'stats'  => array_merge((array) $importJob->stats, $stats),
                ]);
            }
        } catch (\Throwable $e) {
            $importJob->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return redirect()->route('admin.import.index')->with('error', 'Import failed: '.$e->getMessage());
        }

        return redirect()->route('admin.import.index')->with('success', 'Import completed successfully.');
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
