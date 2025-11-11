<?php

declare(strict_types=1);

namespace App\Domain\Membership\Actions;

use App\Domain\Membership\Data\OrderImportData;
use App\Domain\Membership\Data\ProfileData;
use App\Domain\Membership\Enums\MembershipStatus;
use App\Domain\Membership\Models\Season;
use App\Domain\Membership\Models\UserProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ImportCurlingIoOrdersAction
{
    public function __construct(
        private readonly CreateUserFromProfileAction $createUserFromProfile,
        private readonly MatchProductAction $matchProduct,
        private readonly AssignProductToUserAction $assignProductToUser,
    ) {}

    public function execute(string $csvFilePath): array
    {
        $season = Season::query()->where('is_current', true)->first();

        if (! $season) {
            throw new \RuntimeException('No current season found. Please mark a season as current before importing.');
        }

        $handle = fopen($csvFilePath, 'r');
        if (! $handle) {
            throw new \RuntimeException("Unable to open CSV file: {$csvFilePath}");
        }

        // Setup log file in the same directory as the import file
        $logFilePath = dirname($csvFilePath).'/import_log_'.date('Y-m-d_His').'.txt';
        $logHandle = fopen($logFilePath, 'w');
        if (! $logHandle) {
            throw new \RuntimeException("Unable to create log file: {$logFilePath}");
        }

        fwrite($logHandle, 'Import started at '.now()->toDateTimeString()."\n");
        fwrite($logHandle, "CSV file: {$csvFilePath}\n");
        fwrite($logHandle, "Season: {$season->name} (ID: {$season->id})\n");
        fwrite($logHandle, str_repeat('=', 80)."\n\n");

        $header = fgetcsv($handle);
        $stats = [
            'total_orders' => 0,
            'skipped_admin' => 0,
            'skipped_no_profiles' => 0,
            'skipped_no_product_match' => 0,
            'imported_users' => 0,
            'imported_memberships' => 0,
            'updated_memberships' => 0,
            'warnings' => [],
            'unmatched_prices' => [],
            'no_profile_details' => [],
            'log_file_path' => $logFilePath,
        ];

        // Read CSV rows into a collection
        $rows = collect();
        while (($row = fgetcsv($handle)) !== false) {
            $rows->push($row);
        }
        fclose($handle);

        // Process each order using collection methods
        $rows
            ->map(fn ($row) => array_combine($header, $row))
            ->each(function ($data) use (&$stats, $season, $logHandle) {
                $order = OrderImportData::fromCsvRow($data);
                $stats['total_orders']++;

                try {
                    $this->processOrder($order, $season, $stats, $logHandle);
                } catch (\Exception $e) {
                    $warning = "Order {$order->order_id}: {$e->getMessage()}";
                    $stats['warnings'][] = $warning;
                    fwrite($logHandle, "[ERROR] {$warning}\n");
                    Log::warning("Failed to import order {$order->order_id}", [
                        'error' => $e->getMessage(),
                        'order' => $order->toArray(),
                    ]);
                }
            });

        // Write summary to log
        fwrite($logHandle, "\n".str_repeat('=', 80)."\n");
        fwrite($logHandle, "IMPORT SUMMARY\n");
        fwrite($logHandle, str_repeat('=', 80)."\n");
        fwrite($logHandle, "Total Orders: {$stats['total_orders']}\n");
        fwrite($logHandle, "Skipped (Admin Orders): {$stats['skipped_admin']}\n");
        fwrite($logHandle, "Skipped (No Profiles): {$stats['skipped_no_profiles']}\n");
        fwrite($logHandle, "Skipped (No Product Match): {$stats['skipped_no_product_match']}\n");
        fwrite($logHandle, "Users Created/Found: {$stats['imported_users']}\n");
        fwrite($logHandle, "Memberships Imported: {$stats['imported_memberships']}\n");
        fwrite($logHandle, "Memberships Updated: {$stats['updated_memberships']}\n");
        fwrite($logHandle, "\nCompleted at ".now()->toDateTimeString()."\n");

        fclose($logHandle);

        return $stats;
    }

    protected function processOrder(OrderImportData $order, Season $season, array &$stats, $logHandle): void
    {
        if ($order->isAdminOrder()) {
            $stats['skipped_admin']++;
            fwrite($logHandle, "[SKIP-ADMIN] Order {$order->order_id}: Admin order\n");

            return;
        }

        if ($order->profiles->count() === 0) {
            $stats['skipped_no_profiles']++;
            $detail = [
                'order_id' => $order->order_id,
                'user_name' => $order->user_name,
                'user_email' => $order->user_email,
                'total_cents' => $order->total_cents,
                'profiles_raw' => $order->profiles_raw ?: '(empty)',
                'status' => $order->status,
            ];
            $stats['no_profile_details'][] = $detail;
            $stats['warnings'][] = "Order {$order->order_id}: No profiles found in Profiles column";

            fwrite($logHandle, "[SKIP-NO-PROFILES] Order {$order->order_id}\n");
            fwrite($logHandle, "  User: {$order->user_name} ({$order->user_email})\n");
            fwrite($logHandle, '  Total: $'.number_format($order->total_cents / 100, 2)."\n");
            fwrite($logHandle, "  Profiles column: '{$order->profiles_raw}'\n");
            fwrite($logHandle, "  Status: {$order->status}\n\n");

            return;
        }

        $product = $this->matchProduct->execute($order->total_cents, $season);

        if (! $product) {
            $stats['skipped_no_product_match']++;
            $priceKey = $order->total_cents;
            if (! isset($stats['unmatched_prices'][$priceKey])) {
                $stats['unmatched_prices'][$priceKey] = [
                    'price_cents' => $order->total_cents,
                    'price_display' => '$'.number_format($order->total_cents / 100, 2),
                    'count' => 0,
                    'order_ids' => [],
                ];
            }
            $stats['unmatched_prices'][$priceKey]['count']++;
            $stats['unmatched_prices'][$priceKey]['order_ids'][] = $order->order_id;

            $stats['warnings'][] = "Order {$order->order_id}: No product match for price \$".number_format($order->total_cents / 100, 2)." ({$order->total_cents} cents)";

            fwrite($logHandle, "[SKIP-NO-PRODUCT] Order {$order->order_id}\n");
            fwrite($logHandle, '  Price: $'.number_format($order->total_cents / 100, 2)." ({$order->total_cents} cents)\n");
            fwrite($logHandle, "  User: {$order->user_name} ({$order->user_email})\n");
            fwrite($logHandle, "  Profiles: {$order->profiles_raw}\n\n");

            return;
        }

        $profileCount = $order->profiles->count();
        $maxMembers = $product->getMaxMembers();

        if ($profileCount > $maxMembers) {
            $warning = "Order {$order->order_id}: {$profileCount} profiles but product only supports {$maxMembers}. Processing first {$maxMembers} only.";
            $stats['warnings'][] = $warning;
            fwrite($logHandle, "[WARNING] {$warning}\n");
        }

        $status = $order->isPaid() ? MembershipStatus::ACTIVE : MembershipStatus::PENDING;
        $purchaseReference = $order->getPurchaseReference();

        fwrite($logHandle, "[SUCCESS] Order {$order->order_id}\n");
        fwrite($logHandle, "  Product: {$product->name} (ID: {$product->id})\n");
        fwrite($logHandle, '  Price: $'.number_format($product->price_cents / 100, 2)."\n");
        fwrite($logHandle, "  Profiles to process: {$profileCount}\n");
        fwrite($logHandle, "  Status: {$status->value}\n");

        DB::transaction(function () use ($order, $product, $season, $maxMembers, $status, $purchaseReference, &$stats, $logHandle) {
            $order->profiles
                ->take($maxMembers)
                ->each(function ($profileData) use ($product, $season, $order, $status, $purchaseReference, &$stats, $logHandle) {
                    $profile = ProfileData::from($profileData);
                    $user = $this->createUserFromProfile->execute($profile);

                    $existing = UserProduct::query()
                        ->where('user_id', $user->id)
                        ->where('purchase_reference', $purchaseReference)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'status' => $status,
                            'assigned_at' => $order->created_at,
                        ]);
                        $stats['updated_memberships']++;
                        fwrite($logHandle, "    - Updated: {$profile->first_name} {$profile->last_name} ({$profile->email})\n");
                    } else {
                        $this->assignProductToUser->execute(
                            user: $user,
                            product: $product,
                            season: $season,
                            assignedAt: $order->created_at,
                            expiresAt: $season->end_date,
                            purchaseReference: $purchaseReference,
                            metadata: [
                                'imported_from_curlingio' => true,
                                'order_id' => $order->order_id,
                                'import_date' => now()->toDateTimeString(),
                            ],
                            status: $status
                        );
                        $stats['imported_users']++;
                        $stats['imported_memberships']++;
                        fwrite($logHandle, "    - Created: {$profile->first_name} {$profile->last_name} ({$profile->email})\n");
                    }
                });
        });

        fwrite($logHandle, "\n");
    }
}
