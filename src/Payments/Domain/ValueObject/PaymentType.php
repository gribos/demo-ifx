<?php

declare(strict_types=1);

namespace App\Payments\Domain\ValueObject;

use InvalidArgumentException;

class PaymentType
{
    public const DEBIT = 'debit';
    public const CREDIT = 'credit';

    public function __construct(private readonly string $type)
    {
        if (!in_array($type, [self::DEBIT, self::CREDIT])) {
            throw new InvalidArgumentException('Invalid payment type');
        }
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
