<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Membership\Actions\ImportCurlingIoOrderItemsAction;
use Illuminate\Console\Command;

class ImportCurlingIoOrdersCommand extends Command
{
    protected $signature = 'membership:import-curlingio-orders
                            {file : Path to the CSV export file from curling.io}';

    protected $description = 'Import curling.io order items to create members and assign products';

    public function handle(
        ImportCurlingIoOrderItemsAction $importOrderItemsAction
    ): int {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $startTime = microtime(true);

        $this->info('Starting curling.io order import...');
        $this->info("File: {$filePath}");
        $this->newLine();

        try {
            $stats = $importOrderItemsAction->execute($filePath);

            $elapsedTime = microtime(true) - $startTime;

            $this->displayStats($stats);

            // Display unmatched products summary
            if (! empty($stats['unmatched_products'])) {
                $this->newLine();
                $this->warn('Unmatched Products:');
                $this->table(
                    ['Item Name', 'Price', 'Count', 'Sample Order IDs'],
                    collect($stats['unmatched_products'])->map(function ($data) {
                        $sampleIds = array_slice($data['order_ids'], 0, 3);

                        return [
                            $data['item_name'],
                            $data['price_display'],
                            $data['count'],
                            implode(', ', $sampleIds).(count($data['order_ids']) > 3 ? '...' : ''),
                        ];
                    })->toArray()
                );
                $this->line('  These products could not be matched to any products in the current season.');
                $this->line('  Update the ProductMappingConfig or create missing products.');
            }

            if (! empty($stats['warnings'])) {
                $this->newLine();
                $this->warn('Other Warnings:');
                $displayedWarnings = array_filter($stats['warnings'], function ($warning) {
                    return ! str_contains($warning, 'No product match');
                });
                foreach ($displayedWarnings as $warning) {
                    $this->line("  - {$warning}");
                }
            }

            $this->newLine();
            $this->info('Import completed successfully!');
            $this->info("Processing time: {$this->formatElapsedTime($elapsedTime)}");
            $this->info("Log file: {$stats['log_file_path']}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    protected function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Order Items', $stats['total_items']],
                ['Skipped (Adjustments)', $stats['skipped_adjustments']],
                ['Skipped (No Product Match)', $stats['skipped_no_product_match']],
                ['Users Created/Found', $stats['imported_users']],
                ['Memberships Imported', $stats['imported_memberships']],
                ['Memberships Updated', $stats['updated_memberships']],
                ['Couple Memberships', $stats['couple_memberships']],
            ]
        );
    }

    protected function formatElapsedTime(float $seconds): string
    {
        if ($seconds < 60) {
            return number_format($seconds, 2).' seconds';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds - ($minutes * 60);

        if ($minutes < 60) {
            return sprintf('%d minute%s %.2f seconds', $minutes, $minutes > 1 ? 's' : '', $remainingSeconds);
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes - ($hours * 60);

        return sprintf('%d hour%s %d minute%s %.2f seconds', $hours, $hours > 1 ? 's' : '', $remainingMinutes, $remainingMinutes > 1 ? 's' : '', $remainingSeconds);
    }
}
