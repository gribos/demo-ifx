<?php

namespace App\Payments\Infrastructure\Repository;

use App\Payments\Domain\Model\BankAccount;
use App\Payments\Domain\Model\Payment;
use App\Payments\Domain\Repository\BankAccountRepositoryInterface;
use App\Payments\Domain\ValueObject\PaymentType;

class InMemoryBankAccountRepository implements BankAccountRepositoryInterface
{
    private array $accounts = [];

    public function findById(string $id): ?BankAccount
    {
        return $this->accounts[$id] ?? null;
    }

    public function save(BankAccount $bankAccount): void
    {
        $this->accounts[$bankAccount->getId()] = $bankAccount;
    }


    public function getNumberOfPaymentForAccountByDate(string $id, string $transactionDate): int
    {
        if (!$this->accounts[$id]->getPayments()) {
            return 0;
        }
        $todayPayments = array_filter(
            $this->accounts[$id]->getPayments(),
            static fn (Payment $payment) => ($payment->getDate()->format('Y-m-d') === $transactionDate && (string) $payment->getPaymentType() === PaymentType::DEBIT)
        );

        return count($todayPayments);
    }
}
