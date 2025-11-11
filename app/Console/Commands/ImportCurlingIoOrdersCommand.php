<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Membership\Actions\ImportCurlingIoOrderItemsAction;
use App\Domain\Membership\Actions\ImportCurlingIoOrdersAction;
use Illuminate\Console\Command;

class ImportCurlingIoOrdersCommand extends Command
{
    protected $signature = 'membership:import-curlingio-orders
                            {file : Path to the CSV export file from curling.io}
                            {--format=order-items : CSV format: "order-items" (default) or "orders" (legacy)}';

    protected $description = 'Import curling.io order items to create members and assign products';

    public function handle(
        ImportCurlingIoOrderItemsAction $importOrderItemsAction,
        ImportCurlingIoOrdersAction $importOrdersAction
    ): int {
        $filePath = $this->argument('file');
        $format = $this->option('format');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        // Validate format
        if (! in_array($format, ['order-items', 'orders'])) {
            $this->error("Invalid format: {$format}. Must be 'order-items' or 'orders'");

            return self::FAILURE;
        }

        if ($format === 'orders') {
            $this->warn('Using legacy ORDERS format. Consider using ORDER ITEMS format instead (--format=order-items)');
        }

        $this->info('Starting curling.io order import...');
        $this->info("File: {$filePath}");
        $this->info("Format: {$format}");
        $this->newLine();

        try {
            $stats = match ($format) {
                'order-items' => $importOrderItemsAction->execute($filePath),
                'orders' => $importOrdersAction->execute($filePath),
            };

            $this->displayStats($stats, $format);

            // Display unmatched products summary (for order-items)
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

            // Display unmatched prices summary (for legacy orders)
            if (! empty($stats['unmatched_prices'])) {
                $this->newLine();
                $this->warn('Unmatched Product Prices:');
                $this->table(
                    ['Price', 'Orders Count', 'Sample Order IDs'],
                    collect($stats['unmatched_prices'])->map(function ($data) {
                        $sampleIds = array_slice($data['order_ids'], 0, 3);

                        return [
                            $data['price_display'],
                            $data['count'],
                            implode(', ', $sampleIds).(count($data['order_ids']) > 3 ? '...' : ''),
                        ];
                    })->toArray()
                );
                $this->line('  These prices do not match any products in the current season.');
                $this->line('  Create products with these prices or update the orders.');
            }

            // Display "no profiles" summary if there are many (legacy orders only)
            if (isset($stats['skipped_no_profiles']) && $stats['skipped_no_profiles'] > 0) {
                $this->newLine();
                $this->warn("No Profiles Found: {$stats['skipped_no_profiles']} orders");
                $this->line('  These orders have an empty "Profiles" column in the CSV.');
                $this->line('  This typically means the order was placed but no member profiles were added.');
                if (isset($stats['no_profile_details']) && count($stats['no_profile_details']) <= 5) {
                    $this->table(
                        ['Order ID', 'User Name', 'Email', 'Total'],
                        collect($stats['no_profile_details'])->map(fn ($d) => [
                            $d['order_id'],
                            $d['user_name'],
                            $d['user_email'],
                            '$'.number_format($d['total_cents'] / 100, 2),
                        ])->toArray()
                    );
                } else {
                    $this->line("  Check the log file for full details on all {$stats['skipped_no_profiles']} orders.");
                }
            }

            if (! empty($stats['warnings'])) {
                $this->newLine();
                $this->warn('Other Warnings:');
                $displayedWarnings = array_filter($stats['warnings'], function ($warning) {
                    return ! str_contains($warning, 'No product match') && ! str_contains($warning, 'No profiles found');
                });
                foreach ($displayedWarnings as $warning) {
                    $this->line("  - {$warning}");
                }
            }

            $this->newLine();
            $this->info('Import completed successfully!');
            $this->info("Log file: {$stats['log_file_path']}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    protected function displayStats(array $stats, string $format): void
    {
        if ($format === 'order-items') {
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
        } else {
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Orders', $stats['total_orders']],
                    ['Skipped (Admin Orders)', $stats['skipped_admin']],
                    ['Skipped (No Profiles)', $stats['skipped_no_profiles']],
                    ['Skipped (No Product Match)', $stats['skipped_no_product_match']],
                    ['Users Created/Found', $stats['imported_users']],
                    ['Memberships Imported', $stats['imported_memberships']],
                    ['Memberships Updated', $stats['updated_memberships']],
                ]
            );
        }
    }
}
