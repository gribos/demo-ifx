<?php

declare(strict_types=1);

namespace App\Payments\Domain\ValueObject;

use App\Payments\Domain\Exception\CurrencyMismatchException;

readonly class Money
{
    public function __construct(
        private int $amount,
        private Currency $currency
    ) {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $money): Money
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new CurrencyMismatchException('Currencies do not match.');
        }

        return new Money($this->amount + $money->getAmount(), $this->currency);
    }

    public function subtract(Money $money): Money
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new CurrencyMismatchException('Currencies do not match.');
        }

        return new Money($this->amount - $money->getAmount(), $this->currency);
    }

    public function calculateFee(float $fee): Money
    {
        return new Money((int) ($this->amount * ($fee / 100)), $this->currency);
    }

    public function isGreaterThanOrEqualTo(Money $other): bool
    {
        if (!$this->currency->equals($other->getCurrency())) {
            throw new CurrencyMismatchException('Currencies do not match.');
        }

        return $this->amount >= $other->getAmount();
    }
}
