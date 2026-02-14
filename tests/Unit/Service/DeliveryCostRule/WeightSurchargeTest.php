<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DeliveryCostRule;

use App\Model\DeliveryCost;
use App\Model\OrderInterface;
use App\Service\DeliveryCostRule\WeightSurcharge;
use App\ValueObject\Money;
use App\ValueObject\Number;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeightSurchargeTest extends TestCase
{
    private function createOrderStub(
        string $weight = '1',
        string $countryCode = 'PL',
        string $totalPrice = '100'
    ): OrderInterface {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getCountryCode')->willReturn($countryCode);
        $order->method('getWeight')->willReturn(Number::create($weight));
        $order->method('getTotalPrice')->willReturn(Money::create($totalPrice, 'PLN'));
        $order->method('getCreatedAt')->willReturn(new DateTimeImmutable('2026-02-14 12:00:00'));
        return $order;
    }

    private function createDeliveryCost(string $value = '10.00', string $currency = 'PLN'): DeliveryCost
    {
        return new DeliveryCost(Money::create($value, $currency));
    }

    public static function noSurchargeWhenWeightWithinLimitDataProvider(): array
    {
        return [
            '0 kg' => ['0'],
            '1 kg' => ['1'],
            '5 kg exactly' => ['5'],
            '5.00 kg' => ['5.00'],
        ];
    }

    #[DataProvider('noSurchargeWhenWeightWithinLimitDataProvider')]
    public function testIsApplicableReturnsFalseWhenWeightAtOrBelowLimit(string $weight): void
    {
        $rule = new WeightSurcharge();
        $order = $this->createOrderStub($weight);
        $deliveryCost = $this->createDeliveryCost();

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public static function surchargeAppliedDataProvider(): array
    {
        return [
            '5.01 kg -> 1 started kg -> +3 PLN' => ['5.01', '3.00'],
            '6 kg -> 1 started kg -> +3 PLN' => ['6', '3.00'],
            '7.2 kg -> 3 started kg -> +9 PLN' => ['7.2', '9.00'],
            '7.21 kg -> 3 started kg -> +9 PLN' => ['7.21', '9.00'],
            '8 kg -> 3 started kg -> +9 PLN' => ['8', '9.00'],
            '10 kg -> 5 started kg -> +15 PLN' => ['10', '15.00'],
            '15.5 kg -> 11 started kg -> +33 PLN' => ['15.5', '33.00'],
        ];
    }

    #[DataProvider('surchargeAppliedDataProvider')]
    public function testCalculateAddsSurchargeForEachStartedKilogramAboveLimit(string $weight, string $expectedSurcharge): void
    {
        $rule = new WeightSurcharge();
        $order = $this->createOrderStub($weight);
        $deliveryCost = $this->createDeliveryCost('0');

        $this->assertTrue($rule->isApplicable($order, $deliveryCost));
        $rule->calculate($order, $deliveryCost);

        $this->assertSame($expectedSurcharge, $deliveryCost->getValue()->getValue());
    }

    public function testCalculateAddsSurchargeOnTopOfExistingCost(): void
    {
        $rule = new WeightSurcharge();
        $order = $this->createOrderStub('7.2');
        $deliveryCost = $this->createDeliveryCost('10.00');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('19.00', $deliveryCost->getValue()->getValue());
    }

    public function testIsApplicableReturnsFalseWhenCurrencyDoesNotMatch(): void
    {
        $rule = new WeightSurcharge();
        $order = $this->createOrderStub('10');
        $deliveryCost = $this->createDeliveryCost('10.00', 'EUR');

        $this->assertFalse($rule->isApplicable($order, $deliveryCost));
    }

    public function testCalculateWithCustomLimits(): void
    {
        $rule = new WeightSurcharge(
            maxWeightWithoutSurcharge: '10.00',
            surchargePerKg: '2.00'
        );
        $order = $this->createOrderStub('12.5');
        $deliveryCost = $this->createDeliveryCost('0');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('6.00', $deliveryCost->getValue()->getValue());
    }

    public function testWhenWeightExactlyAtLimitNoSurchargeAdded(): void
    {
        $rule = new WeightSurcharge();
        $order = $this->createOrderStub('5.00');
        $deliveryCost = $this->createDeliveryCost('10.00');

        $rule->calculate($order, $deliveryCost);

        $this->assertSame('10.00', $deliveryCost->getValue()->getValue());
    }
}
