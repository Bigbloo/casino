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

    public function creditBalance(int $amountCents): void
    {
        $this->balance_cents += $amountCents;
    }
}
