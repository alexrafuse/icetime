<?php

declare(strict_types=1);

namespace App\Domain\Membership\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class OrderItemImportData extends Data
{
    public function __construct(
        public string $order_id,
        public string $type,
        public string $item_name,
        public string $user_name,
        public string $user_email,
        public ProfileData $profile,
        public ?ProfileData $second_member_profile,
        public string $discounts,
        public int $amount_cents,
        public int $discount_amount_cents,
        public int $hst_cents,
        public int $total_cents,
        public string $status,
        public Carbon $created_at,
        public ?string $name_field = null,
    ) {}

    public static function fromCsvRow(array $row): self
    {
        $profile = ProfileData::fromCsvRow($row);

        $secondMemberProfile = null;
        if (! empty($row['2nd Member Name']) && ! empty($row['2nd Member Email'])) {
            $secondMemberProfile = ProfileData::fromSecondMemberCsvRow($row);
        }

        return new self(
            order_id: $row['Order ID'],
            type: $row['Type'],
            item_name: $row['Item Name'],
            user_name: $row['User Name'],
            user_email: $row['User Email'],
            profile: $profile,
            second_member_profile: $secondMemberProfile,
            discounts: $row['Discounts'] ?? '',
            amount_cents: self::dollarsToCents($row['Amount']),
            discount_amount_cents: self::dollarsToCents($row['Discount Amount']),
            hst_cents: self::dollarsToCents($row['HST']),
            total_cents: self::dollarsToCents($row['Total']),
            status: $row['Status'],
            created_at: Carbon::parse($row['Created']),
            name_field: $row['Name'] ?? null,
        );
    }

    protected static function dollarsToCents(string $dollars): int
    {
        return (int) round((float) $dollars * 100);
    }

    public function isProduct(): bool
    {
        return $this->type === 'Product';
    }

    public function isAdjustment(): bool
    {
        return $this->type === 'Adjustment';
    }

    public function hasSecondMember(): bool
    {
        return $this->second_member_profile !== null;
    }

    public function getPurchaseReference(): string
    {
        return "curlingio_order_{$this->order_id}";
    }
}
