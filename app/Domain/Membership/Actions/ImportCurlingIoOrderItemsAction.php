<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\ImportStats;
use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Services\ImportDataCache;
use App\Domain\Membership\Services\ImportLogger;
use App\Domain\Membership\Services\OrderItemMembershipAssigner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class ImportCurlingIoOrderItemsAction
{
    private ?MatchProductFromOrderItemAction $matchProduct = null;

    private ?OrderItemMembershipAssigner $membershipAssigner = null;

    private ?ProcessAdjustmentAction $processAdjustment = null;

    private ?ImportDataCache $cache = null;

    public function execute(string $csvFilePath): array
    {
        $season = $this->getCurrentSeason();

        // Create cache to load all data into memory
        $this->cache = new ImportDataCache($season);

        // Manually construct actions with cache
        $createUserAction = new CreateUserFromProfileAction($this->cache);
        $assignProductAction = app(AssignProductToUserAction::class);

        $this->matchProduct = new MatchProductFromOrderItemAction($this->cache);
        $this->processAdjustment = new ProcessAdjustmentAction;
        $this->membershipAssigner = new OrderItemMembershipAssigner(
            createUserFromProfile: $createUserAction,
            assignProductToUser: $assignProductAction,
            createCoupleFromOrderItem: new CreateCoupleFromOrderItemAction(
                createUserAction: $createUserAction,
                assignProductAction: $assignProductAction
            ),
            cache: $this->cache
        );

        $logger = $this->createLogger($csvFilePath);
        $stats = new ImportStats(log_file_path: $logger->getPath());

        $logger->writeHeader($csvFilePath, $season->name, $season->id);

        $rows = $this->readCsvFile($csvFilePath);
        $this->processRows($rows, $season, $stats, $logger);

        $logger->writeSummary($stats->toArray());
        $logger->close();

        return $stats->toArray();
    }

    private function getCurrentSeason(): Season
    {
        $season = Season::query()->where('is_current', true)->first();

        if (! $season) {
            throw new \RuntimeException('No current season found. Please mark a season as current before importing.');
        }

        return $season;
    }

    private function createLogger(string $csvFilePath): ImportLogger
    {
        $logFilePath = dirname($csvFilePath).'/import_log_'.date('Y-m-d_His').'.txt';

        return new ImportLogger($logFilePath);
    }

    private function readCsvFile(string $csvFilePath): Collection
    {
        $handle = fopen($csvFilePath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Unable to open CSV file: {$csvFilePath}");
        }

        $header = fgetcsv($handle);
        $rows = collect();

        while (($row = fgetcsv($handle)) !== false) {
            $rows->push(['header' => $header, 'row' => $row]);
        }

        fclose($handle);

        return $rows;
    }

    private function processRows(Collection $rows, Season $season, ImportStats $stats, ImportLogger $logger): void
    {
        $rows
            ->filter(fn ($item) => ! empty(array_filter($item['row']))) // Skip empty rows
            ->map(fn ($item) => $this->padRow($item)) // Pad missing columns
            ->each(fn ($item, $index) => $this->processRow($item, $index, $season, $stats, $logger));
    }

    private function padRow(array $item): array
    {
        $header = $item['header'];
        $row = $item['row'];

        if (count($row) < count($header)) {
            $row = array_pad($row, count($header), '');
        }

        return ['header' => $header, 'row' => $row];
    }

    private function processRow(array $item, int $index, Season $season, ImportStats $stats, ImportLogger $logger): void
    {
        $lineNumber = $index + 2; // +2 because: 0-indexed + header row
        $header = $item['header'];
        $row = $item['row'];

        if (! $this->validateRow($row, $header, $lineNumber, $stats, $logger)) {
            return;
        }

        $data = array_combine($header, $row);
        $stats->incrementTotalItems();

        try {
            $orderItem = OrderItemImportData::fromCsvRow($data);
            $this->processOrderItem($orderItem, $season, $stats, $logger);
        } catch (\Exception $e) {
            $this->handleProcessingError($e, $lineNumber, $data ?? null, $stats, $logger);
        }
    }

    private function validateRow(array $row, array $header, int $lineNumber, ImportStats $stats, ImportLogger $logger): bool
    {
        if (count($row) !== count($header)) {
            $warning = "Line {$lineNumber}: Column count mismatch (expected ".count($header).', got '.count($row).')';
            $stats->addWarning($warning);
            $logger->logError($warning);

            return false;
        }

        return true;
    }

    private function processOrderItem(OrderItemImportData $orderItem, Season $season, ImportStats $stats, ImportLogger $logger): void
    {
        if ($orderItem->isAdjustment()) {
            $this->handleAdjustment($orderItem, $season, $stats, $logger);

            return;
        }

        $product = $this->matchProduct->execute($orderItem);

        if (! $product) {
            $this->handleUnmatchedProduct($orderItem, $stats, $logger);

            return;
        }

        $this->assignMembership($orderItem, $product, $season, $stats, $logger);
    }

    private function handleAdjustment(OrderItemImportData $orderItem, Season $season, ImportStats $stats, ImportLogger $logger): void
    {
        $stats->incrementSkippedAdjustments(); // Keep stats name for backward compat, but we're processing now

        $userProduct = $this->processAdjustment->execute($orderItem, $season);

        if ($userProduct) {
            $logger->logAdjustmentApplied($orderItem, $userProduct);
        } else {
            $logger->logAdjustmentFailed($orderItem);
            $stats->addWarning("Order {$orderItem->order_id}: Could not find product to apply adjustment '{$orderItem->item_name}'");
        }
    }

    private function handleUnmatchedProduct(OrderItemImportData $orderItem, ImportStats $stats, ImportLogger $logger): void
    {
        $stats->incrementSkippedNoProductMatch();
        $stats->addUnmatchedProduct($orderItem);
        $stats->addWarning("Order {$orderItem->order_id}: No product match for '{$orderItem->item_name}' (\$".number_format($orderItem->total_cents / 100, 2).')');

        $logger->logSkippedNoProduct($orderItem);
    }

    private function assignMembership(OrderItemImportData $orderItem, $product, Season $season, ImportStats $stats, ImportLogger $logger): void
    {
        $logger->logSuccessHeader($orderItem, $product, 'active');

        $this->membershipAssigner->assign($orderItem, $product, $season, $stats, $logger);

        $logger->writeBlankLine(); // Empty line after each order
    }

    private function handleProcessingError(\Exception $e, int $lineNumber, ?array $data, ImportStats $stats, ImportLogger $logger): void
    {
        $warning = "Line {$lineNumber}: {$e->getMessage()}";
        $stats->addWarning($warning);
        $logger->logError($warning);

        Log::warning('Failed to import order item', [
            'error' => $e->getMessage(),
            'line_number' => $lineNumber,
            'data' => $data,
        ]);
    }
}
