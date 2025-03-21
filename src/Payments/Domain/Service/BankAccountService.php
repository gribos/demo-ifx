<?php

declare(strict_types=1);

namespace App\Payments\Domain\Service;

use App\Payments\Domain\ValueObject\Money;
use App\Payments\Domain\Repository\BankAccountRepositoryInterface;
use DateTimeImmutable;
use InvalidArgumentException;

class BankAccountService
{
    private BankAccountRepositoryInterface $bankAccountRepository;

    public function __construct(BankAccountRepositoryInterface $bankAccountRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }

    public function credit(string $bankAccountId, Money $amount): void
    {

        $bankAccount = $this->bankAccountRepository->findById($bankAccountId);
        if (!$bankAccount) {
            throw new InvalidArgumentException('Bank account not found.');
        }

        $bankAccount->credit($amount, new DateTimeImmutable());
        $this->bankAccountRepository->save($bankAccount);
    }

    public function debit(string $bankAccountId, Money $amount): void
    {
        $bankAccount = $this->bankAccountRepository->findById($bankAccountId);
        if (!$bankAccount) {
            throw new InvalidArgumentException('Bank account not found.');
        }

        $numberOfDailyPayments = $this->bankAccountRepository->getNumberOfPaymentForAccountByDate(
            $bankAccountId,
            (new DateTimeImmutable())->format('Y-m-d')
        );

        $bankAccount->debit(
            $amount,
            new DateTimeImmutable(),
            $numberOfDailyPayments
        );
        $this->bankAccountRepository->save($bankAccount);
    }
}
