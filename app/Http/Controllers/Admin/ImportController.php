<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportJob;
use App\Services\ShopifyImporter;
use App\Services\WooImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $jobs = ImportJob::query()->orderByDesc('id')->limit(20)->get();

        return view('admin.import.index', compact('jobs'));
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
        $hash = md5_file(storage_path('app/'.$path));

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

            if ($importer && $type === 'products') {
                $preview = $importer->dryRun(storage_path('app/'.$importJob->filename));
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
            if ($importer && $type === 'products') {
                $stats = $importer->import(storage_path('app/'.$importJob->filename));
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
}
