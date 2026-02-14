<?php

declare(strict_types=1);

namespace App\Service\DeliveryCostRule;

use App\Model\OrderInterface;
use App\ValueObject\Number;
use App\Model\DeliveryCostInterface;
use App\Model\DeliveryCostInfoInterface;
use App\ValueObject\NumberInterface;

class ValuePromotion implements DeliveryCostRuleInterface
{
    private readonly NumberInterface $minTotalPriceForPromotion;
    private readonly NumberInterface $defaultDiscount;
    /** @var array<NumberInterface> */
    private readonly array $percentageDiscountPerCountry;

    public function __construct(
        string $minTotalPriceForPromotion = '400',
        string $defaultDiscount = '1.0',
        array $percentageDiscountPerCountry = [
            'USA' => 0.5,
        ],
        private readonly string $applicableCurrencyCode = 'PLN',
        private readonly int $priority = 300,
        private readonly string $label = 'Promocja wartościowa',
    ) {
        $this->minTotalPriceForPromotion = Number::create($minTotalPriceForPromotion);
        $this->defaultDiscount = Number::create('1.00')->sub($defaultDiscount);
        $this->percentageDiscountPerCountry = array_map(fn($discount) => Number::create('1.00')->sub($discount), $percentageDiscountPerCountry);
    }

    public function isApplicable(OrderInterface $order, DeliveryCostInfoInterface $deliveryCost): bool
    {
        return $order->getTotalPrice()->getValue()->toFloat() >= $this->minTotalPriceForPromotion->toFloat()
            && $deliveryCost->getCurrencyCode() === $this->applicableCurrencyCode;
    }

    public function calculate(OrderInterface $order, DeliveryCostInterface $deliveryCost): void
    {
        $promo = $this->percentageDiscountPerCountry[$order->getCountryCode()] ?? $this->defaultDiscount;
        $deliveryCost->multiply($promo);
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function label(): string
    {
        return $this->label;
    }
}
