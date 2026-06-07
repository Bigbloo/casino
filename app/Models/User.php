<?php

namespace App\Models;

use Laravel\Cashier\Billable;

class User
{
    use Billable;

    public int $id;
    public int $balance_cents = 0;

    public static function findOrFail(int $userId): self
    {
        $user = new self();
        $user->id = $userId;

        return $user;
    }

    public static function find(int $userId): ?self
    {
        return self::findOrFail($userId);
    }

    public function creditBalance(int $amountCents, ?string $reference = null): void
    {
        $this->balance_cents += $amountCents;
    }

    public function transactions()
    {
        return new class
        {
            public function where(string $column, string $value): self
            {
                return $this;
            }

            public function exists(): bool
            {
                return false;
            }
        };
    }
}
