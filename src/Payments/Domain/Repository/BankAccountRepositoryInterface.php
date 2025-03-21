<?php

declare(strict_types=1);

namespace App\Payments\Domain\Repository;

use App\Payments\Domain\Model\BankAccount;

interface BankAccountRepositoryInterface
{
    public function findById(string $id): ?BankAccount;
    public function save(BankAccount $bankAccount): void;
    public function getNumberOfPaymentForAccountByDate(string $id, string $transactionDate): int;

}
