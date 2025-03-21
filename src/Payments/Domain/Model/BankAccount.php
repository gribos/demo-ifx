<?php

declare(strict_types=1);

namespace App\Payments\Domain\Model;

use App\Payments\Domain\Exception\CurrencyMismatchException;
use App\Payments\Domain\Exception\InsufficientBalanceException;
use App\Payments\Domain\Exception\TransactionLimitExceededException;
use App\Payments\Domain\ValueObject\Money;
use App\Payments\Domain\ValueObject\Currency;
use App\Payments\Domain\ValueObject\PaymentType;
use DateTimeImmutable;

class BankAccount
{
    private const MAX_DAILY_PAYMENTS = 3;
    private string $id;
    private Money $balance;
    private Currency $currency;

    /** @var Payment[] */
    private array $payments = [];

    public function __construct(string $id, Currency $currency, Money $initialBalance)
    {
        $this->id = $id;
        $this->balance = $initialBalance;
        $this->currency = $currency;
    }

    public function credit(Money $money, DateTimeImmutable $date): void
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new CurrencyMismatchException('Currency must match account currency');
        }
        $this->balance = $this->balance->add($money);
        $this->payments[] = new Payment(
            uniqid('', true),
            $money,
            new PaymentType(PaymentType::CREDIT),
            $date
        );
    }

    public function debit(Money $money, DateTimeImmutable $date, int $numberOfDailyPayments): void
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new CurrencyMismatchException('Currency must match account currency');
        }

        if ($numberOfDailyPayments >= self::MAX_DAILY_PAYMENTS) {
            throw new TransactionLimitExceededException('Daily debit limit exceeded');
        }
        $fee = $money->calculateFee(Payment::DEBIT_FEE_PERCENTAGE);
        $totalAmount = $money->add($fee);
        if (!$this->balance->isGreaterThanOrEqualTo($totalAmount)) {
            throw new InsufficientBalanceException('Insufficient balance for payment');
        }

        $this->balance = $this->balance->subtract($totalAmount);

        $this->payments[] = new Payment(
            uniqid('', true),
            $totalAmount,
            new PaymentType(PaymentType::DEBIT),
            $date
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function setBalance(Money $balance): void
    {
        $this->balance = $balance;
    }

    public function addPayment(Payment $payment): void
    {
        $this->payments[] = $payment;
    }

    /** @return Payment[] */
    public function getPayments(): array
    {
        return $this->payments;
    }
}
