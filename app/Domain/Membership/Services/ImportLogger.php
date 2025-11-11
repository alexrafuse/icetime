<?php

declare(strict_types=1);

namespace App\Domain\Membership\Services;

use App\Domain\Membership\Data\OrderItemImportData;
use App\Domain\Membership\Models\Product;
use Carbon\Carbon;

final class ImportLogger
{
    private $handle;

    public function __construct(
        private readonly string $logFilePath
    ) {
        $this->handle = fopen($this->logFilePath, 'w');
        if (! $this->handle) {
            throw new \RuntimeException("Unable to create log file: {$this->logFilePath}");
        }
    }

    public function writeHeader(string $csvFile, string $seasonName, int $seasonId): void
    {
        $this->write('Order Items Import started at '.Carbon::now()->toDateTimeString());
        $this->write("CSV file: {$csvFile}");
        $this->write("Season: {$seasonName} (ID: {$seasonId})");
        $this->write(str_repeat('=', 80)."\n");
    }

    public function logSkippedAdjustment(OrderItemImportData $orderItem): void
    {
        $this->write("[SKIP-ADJUSTMENT] Order {$orderItem->order_id}: {$orderItem->item_name}");
        $this->write('  Amount: $'.number_format($orderItem->total_cents / 100, 2)."\n");
    }

    public function logAdjustmentApplied(OrderItemImportData $orderItem, $userProduct): void
    {
        $this->write("[ADJUSTMENT-APPLIED] Order {$orderItem->order_id}: {$orderItem->item_name}");
        $this->write('  Refund Amount: $'.number_format(abs($orderItem->total_cents) / 100, 2));
        $this->write("  Applied to: {$userProduct->product->name}");
        $this->write("  User: {$orderItem->user_name} ({$orderItem->user_email})\n");
    }

    public function logAdjustmentFailed(OrderItemImportData $orderItem): void
    {
        $this->write("[ADJUSTMENT-FAILED] Order {$orderItem->order_id}: {$orderItem->item_name}");
        $this->write('  Amount: $'.number_format($orderItem->total_cents / 100, 2));
        $this->write("  User: {$orderItem->user_name} ({$orderItem->user_email})");
        $this->write("  Reason: Could not find matching product to refund\n");
    }

    public function logSkippedNoProduct(OrderItemImportData $orderItem): void
    {
        $this->write("[SKIP-NO-PRODUCT] Order {$orderItem->order_id}");
        $this->write("  Item: {$orderItem->item_name}");
        $this->write('  Price: $'.number_format($orderItem->total_cents / 100, 2)." ({$orderItem->total_cents} cents)");
        $this->write("  User: {$orderItem->user_name} ({$orderItem->user_email})\n");
    }

    public function logSuccessHeader(OrderItemImportData $orderItem, Product $product, string $status): void
    {
        $this->write("[SUCCESS] Order {$orderItem->order_id}");
        $this->write("  Item: {$orderItem->item_name}");
        $this->write("  Product: {$product->name} (ID: {$product->id})");
        $this->write('  Price: $'.number_format($product->price_cents / 100, 2));
        $this->write("  Status: {$status}");
    }

    public function logCoupleMembership(): void
    {
        $this->write('  Type: Couple Membership');
    }

    public function logIndividualMembership(): void
    {
        $this->write('  Type: Individual Membership');
    }

    public function logPrimaryMember(string $firstName, string $lastName, string $email): void
    {
        $this->write("    - Primary: {$firstName} {$lastName} ({$email})");
    }

    public function logPartnerMember(string $firstName, string $lastName, string $email): void
    {
        $this->write("    - Partner: {$firstName} {$lastName} ({$email})");
    }

    public function logUserUpdated(string $firstName, string $lastName, string $email): void
    {
        $this->write("    - Updated: {$firstName} {$lastName} ({$email})");
    }

    public function logUserCreated(string $firstName, string $lastName, string $email): void
    {
        $this->write("    - Created: {$firstName} {$lastName} ({$email})");
    }

    public function logError(string $message): void
    {
        $this->write("[ERROR] {$message}");
    }

    public function writeSummary(array $stats): void
    {
        $this->write('');
        $this->write(str_repeat('=', 80));
        $this->write('IMPORT SUMMARY');
        $this->write(str_repeat('=', 80));
        $this->write("Total Order Items: {$stats['total_items']}");
        $this->write("Skipped (Adjustments): {$stats['skipped_adjustments']}");
        $this->write("Skipped (No Product Match): {$stats['skipped_no_product_match']}");
        $this->write("Users Created/Found: {$stats['imported_users']}");
        $this->write("Memberships Imported: {$stats['imported_memberships']}");
        $this->write("Memberships Updated: {$stats['updated_memberships']}");
        $this->write("Couple Memberships: {$stats['couple_memberships']}");
        $this->write("\nCompleted at ".Carbon::now()->toDateTimeString());
    }

    public function writeBlankLine(): void
    {
        $this->write('');
    }

    private function write(string $message): void
    {
        fwrite($this->handle, $message."\n");
    }

    public function close(): void
    {
        if ($this->handle) {
            fclose($this->handle);
        }
    }

    public function getPath(): string
    {
        return $this->logFilePath;
    }
}
