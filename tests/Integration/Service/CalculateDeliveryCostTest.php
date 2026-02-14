<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Model\Order;
use App\Service\CalculateDeliveryCost;
use App\ValueObject\Money;
use App\ValueObject\Number;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class CalculateDeliveryCostTest extends KernelTestCase
{
    private CalculateDeliveryCost $calculateDeliveryCost;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->calculateDeliveryCost = self::getContainer()->get(CalculateDeliveryCost::class);
    }

    private function createOrder(
        string $countryCode = 'PL',
        string $weight = '1',
        string $totalPrice = '100',
        ?DateTimeImmutable $createdAt = null
    ): Order {
        return new Order(
            Number::create($weight),
            Money::create($totalPrice, 'PLN'),
            $countryCode,
            $createdAt ?? new DateTimeImmutable('2024-01-15 12:00:00'),
            []
        );
    }

    public function testPolandLightPackageBelow400ReturnsBaseRateOnly(): void
    {
        $order = $this->createOrder('PL', '3', '100');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('10.00', $cost->getValue()->getValue());
        $this->assertSame('PLN', $cost->getCurrencyCode());
    }

    public function testPoland72KgBelow400ReturnsBasePlusWeightSurcharge(): void
    {
        $order = $this->createOrder('PL', '7.2', '100');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('19.00', $cost->getValue()->getValue());
    }

    public function testGermanyReturns20Pln(): void
    {
        $order = $this->createOrder('DE', '1', '50');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('20.00', $cost->getValue()->getValue());
    }

    public function testUsaReturns50Pln(): void
    {
        $order = $this->createOrder('USA', '1', '50');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('50.00', $cost->getValue()->getValue());
    }

    public function testOtherCountryReturns3999(): void
    {
        $order = $this->createOrder('FR', '1', '50');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('39.99', $cost->getValue()->getValue());
    }

    public function testPolandBasket400OrMoreFreeDelivery(): void
    {
        $order = $this->createOrder('PL', '2', '400');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('0.00', $cost->getValue()->getValue());
    }

    public function testUsaBasket400OrMore50PercentOff(): void
    {
        $order = $this->createOrder('USA', '1', '500');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('25.00', $cost->getValue()->getValue());
    }

    public function testFridayApplies50PercentDiscount(): void
    {
        $friday = new DateTimeImmutable('2024-01-19 12:00:00');
        $order = $this->createOrder('PL', '1', '100', $friday);

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('5.00', $cost->getValue()->getValue());
    }

    public function testFridayAndUsaCombinedDiscounts(): void
    {
        $friday = new DateTimeImmutable('2024-01-19 12:00:00');
        $order = $this->createOrder('USA', '1', '200', $friday);

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('25.00', $cost->getValue()->getValue());
    }

    public function testFridayDoesNotApplyWhenDeliveryAlreadyFree(): void
    {
        $friday = new DateTimeImmutable('2024-01-19 12:00:00');
        $order = $this->createOrder('PL', '1', '500', $friday);

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('0.00', $cost->getValue()->getValue());
    }

    public function testWeight5KgNoSurcharge(): void
    {
        $order = $this->createOrder('PL', '5', '100');

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('10.00', $cost->getValue()->getValue());
    }

    public function testRulesAppliedInCorrectOrder(): void
    {
        $friday = new DateTimeImmutable('2024-01-19 12:00:00');
        $order = $this->createOrder('USA', '7.2', '100', $friday);

        $cost = $this->calculateDeliveryCost->execute($order);

        $this->assertSame('29.50', $cost->getValue()->getValue());
    }
}
