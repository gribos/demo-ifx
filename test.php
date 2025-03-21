<?php
require 'vendor/autoload.php';

use App\Payments\Domain\Model\BankAccount;
use App\Payments\Domain\Service\BankAccountService;
use App\Payments\Domain\ValueObject\Currency;
use App\Payments\Domain\ValueObject\Money;
use App\Payments\Infrastructure\Repository\InMemoryBankAccountRepository;

$currency = new Currency('PLN');

$bankAccount = new BankAccount(1, $currency, new Money(10000, $currency));
$bankRepository = new InMemoryBankAccountRepository();
$bankRepository->save($bankAccount);
$bankAccountService = new BankAccountService($bankRepository);
$bankAccountService->credit(1, new Money(3000, $currency));
$bankAccountService->credit(1, new Money(7000, $currency));
$bankAccountService->debit(1, new Money(4000, $currency));

echo($bankAccount->getBalance()->getAmount());


