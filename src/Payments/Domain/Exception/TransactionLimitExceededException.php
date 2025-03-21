<?php

declare(strict_types=1);

namespace App\Payments\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

class TransactionLimitExceededException extends DomainException
{
}
