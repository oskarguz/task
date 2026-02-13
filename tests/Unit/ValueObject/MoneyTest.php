<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use App\ValueObject\Money;
use InvalidArgumentException;

class MoneyTest extends TestCase
{
    public function testCreate(): void
    {
        $money = Money::create(10.5, 'USD');
        $this->assertSame('10.50', $money->getValueScalar());
        $this->assertSame('10.50 USD', $money->getFormatted());
    }

    public function testAdd(): void
    {
        $money1 = Money::create(10.0, 'USD');
        $money2 = Money::create(5.5, 'USD');
        $result = $money1->add($money2);
        $this->assertSame('15.50', $result->getValueScalar());
        $this->assertSame('15.50 USD', $result->getFormatted());
    }

    public function testAddWithDifferentCurrencies(): void
    {
        $money1 = Money::create(10.0, 'USD');
        $money2 = Money::create(5.5, 'EUR');

        $this->expectException(InvalidArgumentException::class);
        $money1->add($money2);
    }
}
