<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DeliveryCostRule;

use App\Model\DeliveryCost;
use App\Model\OrderInterface;
use App\Service\DeliveryCostRule\FridayPromotion;
use App\ValueObject\Money;
use App\ValueObject\Number;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FridayPromotionTest extends TestCase
{
    private function createOrderStub(?DateTimeImmutable $createdAt = null, string $countryCode = 'PL'): OrderInterface
    {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getCountryCode')->willReturn($countryCode);
        $order->method('getWeight')->willReturn(Number::create('1'));
        $order->method('getTotalPrice')->willReturn(Money::create('100', 'PLN'));
        $order->method('getCreatedAt')->willReturn($createdAt ?? new DateTimeImmutable('2026-02-14 12:00:00'));
        return $order;
    }

    private function createDeliveryCost(string $value = '20.00', string $currency = 'PLN'): DeliveryCost
    {
        return new DeliveryCost(Money::create($value, $currency));
    }

    public function testApplies50PercentDiscountOnFriday(): void
    {
        $rule = new FridayPromotion();
        $friday = new DateTimeImmutable('2026-02-13 12:00:00');
        $this->assertSame('5', $friday->format('N'));

        $order = $this->createOrderStub($friday);
        $deliveryCost = $this->createDeliveryCost('20.00');

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
        $rule->calculate($order, $deliveryCost);

        $this->assertSame('10.00', $deliveryCost->getValue()->getValue());
    }

    public static function notFridayDataProvider(): array
    {
        return [
            'Monday' => [new DateTimeImmutable('2026-02-09 12:00:00')],
            'Tuesday' => [new DateTimeImmutable('2026-01-10 12:00:00')],
            'Wednesday' => [new DateTimeImmutable('2026-01-11 12:00:00')],
            'Thursday' => [new DateTimeImmutable('2026-01-12 12:00:00')],
            'Saturday' => [new DateTimeImmutable('2026-01-14 12:00:00')],
            'Sunday' => [new DateTimeImmutable('2026-01-15 12:00:00')],
        ];
    }

    #[DataProvider('notFridayDataProvider')]
    public function testIsApplicableReturnsFalseWhenNotFriday(DateTimeImmutable $createdAt): void
    {
        $rule = new FridayPromotion();
        $order = $this->createOrderStub($createdAt);
        $deliveryCost = $this->createDeliveryCost('20.00');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testDoesNotApplyWhenDeliveryIsAlreadyFree(): void
    {
        $rule = new FridayPromotion();
        $friday = new DateTimeImmutable('2026-02-13 12:00:00');
        $order = $this->createOrderStub($friday);
        $deliveryCost = $this->createDeliveryCost('0.00');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testIsApplicableReturnsFalseWhenCurrencyDoesNotMatch(): void
    {
        $rule = new FridayPromotion();
        $friday = new DateTimeImmutable('2026-02-13 12:00:00');
        $order = $this->createOrderStub($friday);
        $deliveryCost = $this->createDeliveryCost('20.00', 'EUR');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testCalculateHalvesTheCost(): void
    {
        $rule = new FridayPromotion();
        $friday = new DateTimeImmutable('2026-02-13 12:00:00');
        $order = $this->createOrderStub($friday);
        $deliveryCost = $this->createDeliveryCost('50.00');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('25.00', $deliveryCost->getValue()->getValue());
    }

    public function testCustomDiscountFactor(): void
    {
        $rule = new FridayPromotion(discount: '0.25');
        $friday = new DateTimeImmutable('2026-02-13 12:00:00');
        $order = $this->createOrderStub($friday);
        $deliveryCost = $this->createDeliveryCost('20.00');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('15.00', $deliveryCost->getValue()->getValue());
    }
}
