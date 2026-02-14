<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DeliveryCostRule;

use App\Model\DeliveryCost;
use App\Model\OrderInterface;
use App\Service\DeliveryCostRule\BaseRateByCountry;
use App\ValueObject\Money;
use App\ValueObject\Number;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BaseRateByCountryTest extends TestCase
{
    private function createOrderStub(
        string $countryCode = 'PL',
        string $weight = '1',
        string $totalPrice = '100',
        ?DateTimeImmutable $createdAt = null
    ): OrderInterface {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getCountryCode')->willReturn($countryCode);
        $order->method('getWeight')->willReturn(Number::create($weight));
        $order->method('getTotalPrice')->willReturn(Money::create($totalPrice, 'PLN'));
        $order->method('getCreatedAt')->willReturn($createdAt ?? new DateTimeImmutable('2026-02-14 12:00:00'));
        return $order;
    }

    private function createDeliveryCost(string $value = '0', string $currency = 'PLN'): DeliveryCost
    {
        return new DeliveryCost(Money::create($value, $currency));
    }

    public static function baseRateByCountryDataProvider(): array
    {
        return [
            ['PL', '10.00'],
            ['DE', '20.00'],
            ['USA', '50.00'],
        ];
    }

    #[DataProvider('baseRateByCountryDataProvider')]
    public function testCalculateAppliesCorrectBaseRateForCountry(string $countryCode, string $expectedRate): void
    {
        $rule = new BaseRateByCountry();
        $order = $this->createOrderStub($countryCode);
        $deliveryCost = $this->createDeliveryCost();

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
        $rule->calculate($order, $deliveryCost);

        $this->assertSame($expectedRate, $deliveryCost->getValue()->getValue());
        $this->assertSame('PLN', $deliveryCost->getCurrencyCode());
    }

    public function testCalculateAppliesDefaultRateForOtherCountries(): void
    {
        $defaultBaseRate = '66.66';
        $rule = new BaseRateByCountry(defaultBaseRate: $defaultBaseRate);
        $order = $this->createOrderStub('FR');
        $deliveryCost = $this->createDeliveryCost();

        $rule->calculate($order, $deliveryCost);

        $this->assertSame($defaultBaseRate, $deliveryCost->getValue()->getValue());
    }

    public function testCalculateAppliesDefaultRateForUnknownCountryCode(): void
    {
        $defaultBaseRate = '66.66';
        $rule = new BaseRateByCountry(defaultBaseRate: $defaultBaseRate);
        $order = $this->createOrderStub('XX');
        $deliveryCost = $this->createDeliveryCost();

        $rule->calculate($order, $deliveryCost);

        $this->assertSame($defaultBaseRate, $deliveryCost->getValue()->getValue());
    }

    public function testIsApplicableReturnsTrueWhenCurrencyMatches(): void
    {
        $rule = new BaseRateByCountry();
        $order = $this->createOrderStub('PL');
        $deliveryCost = $this->createDeliveryCost('0', 'PLN');

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
    }

    public function testIsApplicableReturnsFalseWhenCurrencyDoesNotMatch(): void
    {
        $rule = new BaseRateByCountry();
        $order = $this->createOrderStub('PL');
        $deliveryCost = $this->createDeliveryCost('0', 'EUR');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testCalculateWithCustomRates(): void
    {
        $rule = new BaseRateByCountry(
            defaultBaseRate: '25.00',
            baseRatesPerCountry: [
                'PL' => '15.00',
                'CZ' => '18.00',
            ]
        );

        $costPl = $this->createDeliveryCost();
        $rule->calculate($this->createOrderStub('PL'), $costPl);
        $this->assertSame('15.00', $costPl->getValue()->getValue());

        $costCz = $this->createDeliveryCost();
        $rule->calculate($this->createOrderStub('CZ'), $costCz);
        $this->assertSame('18.00', $costCz->getValue()->getValue());

        $costOther = $this->createDeliveryCost();
        $rule->calculate($this->createOrderStub('SK'), $costOther);
        $this->assertSame('25.00', $costOther->getValue()->getValue());
    }
}
