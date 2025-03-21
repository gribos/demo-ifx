<?php

declare(strict_types=1);

namespace App\Payments\Domain\Model;

use App\Payments\Domain\ValueObject\Money;
use App\Payments\Domain\ValueObject\PaymentType;
use DateTimeImmutable;

class Payment
{
    public const DEBIT_FEE_PERCENTAGE = 0.5;

    public function __construct(
        private readonly string $id,
        private readonly Money $amount,
        private readonly PaymentType $paymentType,
        private readonly DateTimeImmutable $date
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getPaymentType(): PaymentType
    {
        return $this->paymentType;
    }
}
