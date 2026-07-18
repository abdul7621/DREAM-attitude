<?php

namespace App\Console\Commands;

use App\Services\WooImporter;
use Illuminate\Console\Command;

class ImportWooOrders extends Command
{
    protected $signature = 'woo:import-orders {file : Path to WooCommerce orders CSV/TSV file} {--dry-run : Preview import without making changes}';

    protected $description = 'Import orders from WooCommerce TSV export (ruby_orders.csv)';

    public function __construct(private readonly WooImporter $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("📂 File: {$filePath}");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN — No changes will be made');
            $this->newLine();

            $stats = $this->importer->dryRunOrders($filePath);

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total orders', $stats['total_orders']],
                    ['Completed (→ delivered)', $stats['completed']],
                    ['Cancelled', $stats['cancelled']],
                    ['Linked to existing user', $stats['linked']],
                    ['Unlinked (guest/no match)', $stats['unlinked']],
                    ['Already imported (skip)', $stats['existing_skip'] ?? 0],
                ]
            );

            $this->info('✅ Dry run complete. Run without --dry-run to import.');
            return self::SUCCESS;
        }

        $this->info('🚀 Importing orders...');
        $this->newLine();

        $result = $this->importer->importOrders($filePath);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Orders created', $result['orders']],
                ['Linked to user', $result['linked']],
                ['Unlinked (guest)', $result['unlinked']],
                ['Skipped (already imported)', $result['skipped']],
                ['Errors', count($result['errors'])],
            ]
        );

        if (!empty($result['errors'])) {
            $this->newLine();
            $this->warn('⚠️  Errors:');
            foreach (array_slice($result['errors'], 0, 20) as $err) {
                $this->line("  • {$err}");
            }
            if (count($result['errors']) > 20) {
                $this->line('  ... and ' . (count($result['errors']) - 20) . ' more');
            }
        }

        $this->newLine();
        $this->info('✅ Order import complete!');

        return self::SUCCESS;
    }
}
