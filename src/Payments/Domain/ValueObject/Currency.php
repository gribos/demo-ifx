<?php

declare(strict_types=1);

namespace App\Payments\Domain\ValueObject;

class Currency
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->getCode();
    }
}
