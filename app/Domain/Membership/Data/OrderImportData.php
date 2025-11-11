<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class OrderImportData extends Data
{
    public function __construct(
        public string $order_id,
        public string $status,
        public string $user_name,
        public string $user_email,
        public string $profiles_raw,
        #[DataCollectionOf(ProfileData::class)]
        public DataCollection $profiles,
        public int $amount_cents,
        public int $discount_amount_cents,
        public int $hst_cents,
        public int $total_cents,
        public int $amount_paid_cents,
        public int $amount_refunded_cents,
        public int $amount_owing_cents,
        public Carbon $created_at,
        public Carbon $updated_at,
    ) {}

    public static function fromCsvRow(array $row): self
    {
        $profilesRaw = trim($row['Profiles'] ?? '');
        $profiles = self::parseProfiles($profilesRaw, $row['User Email']);

        return new self(
            order_id: $row['ID'],
            status: $row['Status'],
            user_name: $row['User Name'],
            user_email: $row['User Email'],
            profiles_raw: $profilesRaw,
            profiles: $profiles,
            amount_cents: self::dollarsToCents($row['Amount']),
            discount_amount_cents: self::dollarsToCents($row['Discount amount']),
            hst_cents: self::dollarsToCents($row['HST']),
            total_cents: self::dollarsToCents($row['Total']),
            amount_paid_cents: self::dollarsToCents($row['Amount paid']),
            amount_refunded_cents: self::dollarsToCents($row['Amount refunded']),
            amount_owing_cents: self::dollarsToCents($row['Amount owing']),
            created_at: Carbon::parse($row['Created']),
            updated_at: Carbon::parse($row['Updated']),
        );
    }

    protected static function parseProfiles(string $profilesRaw, string $userEmail): DataCollection
    {
        if (empty($profilesRaw)) {
            return new DataCollection(ProfileData::class, []);
        }

        $profiles = collect(explode(',', $profilesRaw))
            ->map(fn ($name) => trim($name))
            ->map(fn ($name, $index) => ProfileData::fromProfileString($name, $userEmail, $index === 0))
            ->toArray();

        return new DataCollection(ProfileData::class, $profiles);
    }

    protected static function dollarsToCents(string $dollars): int
    {
        return (int) round((float) $dollars * 100);
    }

    public function isPaid(): bool
    {
        return $this->amount_owing_cents === 0;
    }

    public function isAdminOrder(): bool
    {
        return str_contains(strtolower($this->user_email), 'jefflapierre');
    }

    public function getPurchaseReference(): string
    {
        return "curlingio_order_{$this->order_id}";
    }
}
