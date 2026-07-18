<?php

namespace App\Console\Commands;

use App\Services\WooImporter;
use Illuminate\Console\Command;

class ImportWooCustomers extends Command
{
    protected $signature = 'woo:import-customers {file : Path to WooCommerce customers CSV/TSV file} {--dry-run : Preview import without making changes}';

    protected $description = 'Import customers from WooCommerce CSV/TSV export (ruby_customers_detailed.csv or ruby_users_all.csv)';

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

            $stats = $this->importer->dryRunCustomers($filePath);

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total in file', $stats['total_in_file']],
                    ['New customers (will import)', $stats['new_customers']],
                    ['Already existing (skip)', $stats['existing_skip']],
                    ['Bot/spam filtered', $stats['bot_filtered']],
                    ['Addresses to create', $stats['addresses_to_create'] ?? $stats['addresses'] ?? 0],
                ]
            );

            $this->info('✅ Dry run complete. Run without --dry-run to import.');
            return self::SUCCESS;
        }

        $this->info('🚀 Importing customers...');
        $this->newLine();

        $result = $this->importer->importCustomers($filePath);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Customers created', $result['customers']],
                ['Addresses created', $result['addresses']],
                ['Skipped (existing)', $result['skipped']],
                ['Bot/spam filtered', $result['bot_filtered']],
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
        $this->info('✅ Import complete! All imported users have must_reset_password = true.');

        return self::SUCCESS;
    }
}
