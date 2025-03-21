# Bank Account and Payments System
This project is a simple Bank Account and Payments System implemented in PHP 8.x using Domain-Driven Design (DDD) principles. It allows managing bank accounts, performing credit and debit operations, and handling payments with currency validation, transaction fees, and daily transaction limits.

## Getting Started
#### Prerequisites
1. PHP 8.x
2. Composer (for autoloading and dependencies)

### Installation
1. Clone the repository
2. run docker <pre>docker compose up --build</pre>
3. Install dependencies <pre>docker compose exec php composer install</pre>
4. Run the unit tests: <pre>docker compose exec php vendor/bin/phpunit tests</pre>

### Usage Example
<pre>
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
</pre>

### Project Improvements and Development Ideas
1. integration with Symfony API Platform
   2. Create a RESTful API for BankAccount and Payment.
2. Introduce CQRS
3. Add Repository and Infrastructure Layer
4. More unit tests for example for Infrastructure
 