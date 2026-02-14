<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DeliveryCostRule;

use App\Model\DeliveryCost;
use App\Model\OrderInterface;
use App\Service\DeliveryCostRule\ValuePromotion;
use App\ValueObject\Money;
use App\ValueObject\Number;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValuePromotionTest extends TestCase
{
    private function createOrderStub(
        string $totalPrice = '100',
        string $countryCode = 'PL',
        string $weight = '1'
    ): OrderInterface {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getCountryCode')->willReturn($countryCode);
        $order->method('getWeight')->willReturn(Number::create($weight));
        $order->method('getTotalPrice')->willReturn(Money::create($totalPrice, 'PLN'));
        $order->method('getCreatedAt')->willReturn(new DateTimeImmutable('2026-02-14 12:00:00'));
        return $order;
    }

    private function createDeliveryCost(string $value = '20.00', string $currency = 'PLN'): DeliveryCost
    {
        return new DeliveryCost(Money::create($value, $currency));
    }

    public static function calculateAppliesCorrectDiscountDataProvider(): array
    {
        return [
            'PL, 400 PLN → free delivery' => ['400', 'PL', '20.00', '0.00'],
            'DE, 500 PLN → free delivery' => ['500', 'DE', '20.00', '0.00'],
            'USA, 400 PLN → 50% off' => ['400', 'USA', '50.00', '25.00'],
            'USA, 1000 PLN → 50% off' => ['1000', 'USA', '50.00', '25.00'],
        ];
    }

    #[DataProvider('calculateAppliesCorrectDiscountDataProvider')]
    public function testCalculateAppliesCorrectDiscount(
        string $totalPrice,
        string $countryCode,
        string $initialDeliveryCost,
        string $expectedCost
    ): void {
        $rule = new ValuePromotion();
        $order = $this->createOrderStub($totalPrice, $countryCode);
        $deliveryCost = $this->createDeliveryCost($initialDeliveryCost);

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
        $rule->calculate($order, $deliveryCost);

        $this->assertSame($expectedCost, $deliveryCost->getValue()->getValue());
    }

    public static function belowThresholdDataProvider(): array
    {
        return [
            '399' => ['399'],
            '399.99' => ['399.99'],
            '0' => ['0'],
            '100' => ['100'],
        ];
    }

    #[DataProvider('belowThresholdDataProvider')]
    public function testIsApplicableReturnsFalseWhenBasketBelow400(string $totalPrice): void
    {
        $rule = new ValuePromotion();
        $order = $this->createOrderStub($totalPrice, 'PL');
        $deliveryCost = $this->createDeliveryCost();

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testIsApplicableReturnsTrueWhenBasketExactly400(): void
    {
        $rule = new ValuePromotion();
        $order = $this->createOrderStub('400', 'PL');
        $deliveryCost = $this->createDeliveryCost();

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
    }

    public function testIsApplicableReturnsFalseWhenCurrencyDoesNotMatch(): void
    {
        $rule = new ValuePromotion();
        $order = $this->createOrderStub('500', 'PL');
        $deliveryCost = $this->createDeliveryCost('20.00', 'EUR');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testUsaDiscountMultipliesByHalf(): void
    {
        $rule = new ValuePromotion();
        $order = $this->createOrderStub('400', 'USA');
        $deliveryCost = $this->createDeliveryCost('100.00');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('50.00', $deliveryCost->getValue()->getValue());
    }

    public function testCustomMinTotalPrice(): void
    {
        $rule = new ValuePromotion(minTotalPriceForPromotion: '200');
        $order = $this->createOrderStub('200', 'PL');
        $deliveryCost = $this->createDeliveryCost('15.00');

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
        $rule->calculate($order, $deliveryCost);
        $this->assertSame('0.00', $deliveryCost->getValue()->getValue());
    }
}
