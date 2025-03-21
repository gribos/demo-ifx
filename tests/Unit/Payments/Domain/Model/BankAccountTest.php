<?php

declare(strict_types=1);

namespace Tests\Payments\Domain\Model;

use App\Payments\Domain\Model\BankAccount;
use App\Payments\Domain\ValueObject\Money;
use App\Payments\Domain\ValueObject\Currency;
use App\Payments\Domain\Exception\CurrencyMismatchException;
use App\Payments\Domain\Exception\InsufficientBalanceException;
use App\Payments\Domain\Exception\TransactionLimitExceededException;
use App\Payments\Domain\ValueObject\PaymentType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    private BankAccount $bankAccount;
    private Currency $pln;

    protected function setUp(): void
    {
        $this->pln = new Currency('PLN');
        $initialBalance = new Money(10000, $this->pln);
        $this->bankAccount = new BankAccount('bac-1', $this->pln, $initialBalance);
    }

    public function testInitialState(): void
    {
        $this->assertEquals('bac-1', $this->bankAccount->getId());
        $this->assertEquals(10000, $this->bankAccount->getBalance()->getAmount());
        $this->assertEquals('PLN', $this->bankAccount->getCurrency()->getCode());
        $this->assertEmpty($this->bankAccount->getPayments());
    }

    public function testCreditIncreasesBalanceAndAddsPayment(): void
    {
        $money = new Money(5000, $this->pln);
        $date = new DateTimeImmutable('2025-03-20');
        $this->bankAccount->credit($money, $date);

        $this->assertEquals(15000, $this->bankAccount->getBalance()->getAmount());
        $this->assertEquals('PLN', $this->bankAccount->getCurrency()->getCode());
        $payments = $this->bankAccount->getPayments();
        $this->assertCount(1, $payments);
        $this->assertEquals(5000, $payments[0]->getAmount()->getAmount());
        $this->assertEquals(PaymentType::CREDIT, $payments[0]->getPaymentType());
    }

    public function testCreditWithWrongCurrencyThrowsException(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $money = new Money(5000, new Currency('USD'));
        $this->bankAccount->credit($money, new DateTimeImmutable());
    }

    public function testDebitDecreasesBalanceWithFeeAndAddsPayment(): void
    {
        $paymentDate = new DateTimeImmutable('2025-03-20');
        $this->bankAccount->debit(
            new Money(1000, $this->pln),
            $paymentDate,
            0
        );

        $expectedFee = 5; // 0.5% - 1000
        $expectedTotal = 1000 + $expectedFee;
        $payments = $this->bankAccount->getPayments();
        $lastPayment = end($payments);
        $this->assertEquals(10000 - $expectedTotal, $this->bankAccount->getBalance()->getAmount());
        $this->assertCount(1, $payments);
        $this->assertEquals($expectedTotal, $lastPayment->getAmount()->getAmount());
        $this->assertEquals(PaymentType::DEBIT, $lastPayment->getPaymentType());
        $this->assertEquals($paymentDate, $lastPayment->getDate());
        $this->assertEquals($this->pln, $lastPayment->getAmount()->getCurrency());
    }

    public function testDebitWithWrongCurrencyThrowsException(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $money = new Money(1000, new Currency('USD'));
        $this->bankAccount->debit($money, new DateTimeImmutable(), 0);
    }

    public function testDebitWithInsufficientBalanceThrowsException(): void
    {
        $this->expectException(InsufficientBalanceException::class);
        $money = new Money(10000, $this->pln);
        $this->bankAccount->debit($money, new DateTimeImmutable(), 0);
    }

    public function testDebitExceedingDailyLimitThrowsException(): void
    {
        $this->expectException(TransactionLimitExceededException::class);
        $money = new Money(1000, $this->pln);
        $this->bankAccount->debit($money, new DateTimeImmutable(), 3);
    }

    public function testMultipleDebitsWithinLimit(): void
    {
        $money = new Money(2000, $this->pln);
        $date = new DateTimeImmutable('2025-03-20');

        $this->bankAccount->debit($money, $date, 0);
        $this->bankAccount->debit($money, $date, 1);
        $this->bankAccount->debit($money, $date, 2);

        $expectedFee = 2000 * 0.005; // 0.5% z 200.0
        $expectedTotalPerDebit = 2000 + $expectedFee;
        $expectedBalance = 10000 - (3 * $expectedTotalPerDebit);
        $this->assertEquals($expectedBalance, $this->bankAccount->getBalance()->getAmount());
        $this->assertCount(3, $this->bankAccount->getPayments());
    }
}
