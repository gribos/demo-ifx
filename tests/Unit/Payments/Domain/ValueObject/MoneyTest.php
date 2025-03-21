<?php

declare(strict_types=1);

namespace Tests\Payments\Domain\ValueObject;

use App\Payments\Domain\Exception\CurrencyMismatchException;
use App\Payments\Domain\ValueObject\Currency;
use App\Payments\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    private Currency $pln;
    private Money $money;

    protected function setUp(): void
    {
        $this->pln = new Currency('PLN');
        $this->money = new Money(1000, $this->pln);
    }

    public function testConstructionAndGetters(): void
    {
        $this->assertEquals(1000, $this->money->getAmount());
        $this->assertSame($this->pln, $this->money->getCurrency());
    }

    public function testAddWithSameCurrency(): void
    {
        $moneyToAdd= new Money(500, $this->pln);
        $result = $this->money->add($moneyToAdd);

        $this->assertEquals(1500, $result->getAmount());
        $this->assertSame($this->pln, $result->getCurrency());
        $this->assertNotSame($this->money, $result);
    }

    public function testAddWithDifferentCurrencyThrowsException(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $moneyToAdd = new Money(500, new Currency('USD'));
        $this->money->add($moneyToAdd);
    }

    public function testSubtractWithSameCurrency(): void
    {
        $moneyToSub = new Money(300, $this->pln);
        $result = $this->money->subtract($moneyToSub);

        $this->assertEquals(700, $result->getAmount());
        $this->assertSame($this->pln, $result->getCurrency());
        $this->assertNotSame($this->money, $result);
    }

    public function testSubtractWithDifferentCurrencyThrowsException(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $moneyToSub = new Money(300, new Currency('USD'));
        $this->money->subtract($moneyToSub);
    }

    public function testCalculateFee(): void
    {
        $feePercentage = 0.5;
        $fee = $this->money->calculateFee($feePercentage);

        $expectedFee = (int) (1000 * (0.5 / 100));
        $this->assertEquals($expectedFee, $fee->getAmount());
        $this->assertSame($this->pln, $fee->getCurrency());
    }

    public function testCalculateFeeWithZeroPercentage(): void
    {
        $fee = $this->money->calculateFee(0);

        $this->assertEquals(0, $fee->getAmount());
        $this->assertSame($this->pln, $fee->getCurrency());
    }

    public function testIsGreaterThanOrEqualToWithSameCurrency(): void
    {
        $monetEqual = new Money(1000, $this->pln);
        $moneyLess = new Money(500, $this->pln);
        $moneyGreater = new Money(1500, $this->pln);

        $this->assertTrue($this->money->isGreaterThanOrEqualTo($monetEqual));
        $this->assertTrue($this->money->isGreaterThanOrEqualTo($moneyLess));
        $this->assertFalse($this->money->isGreaterThanOrEqualTo($moneyGreater));
    }

    public function testIsGreaterThanOrEqualToWithDifferentCurrencyThrowsException(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        $wrongCurrencyMoney = new Money(1000, new Currency('USD'));
        $this->money->isGreaterThanOrEqualTo($wrongCurrencyMoney);
    }
}
