<?php

declare(strict_types=1);

namespace App\Service\DeliveryCostRule;

use App\Model\OrderInterface;
use App\Model\DeliveryCostInterface;
use App\Model\DeliveryCostInfoInterface;
use App\ValueObject\NumberInterface;
use App\ValueObject\Number;

class WeightSurcharge implements DeliveryCostRuleInterface
{
    private readonly NumberInterface $maxWeightWithoutSurcharge;
    private readonly NumberInterface $surchargePerKg;

    public function __construct(
        string $maxWeightWithoutSurcharge = '5.00',
        string $surchargePerKg = '3.00',
        private readonly string $currencyCode = 'PLN',
        private readonly int $priority = 200,
        private readonly string $label = 'Dopłata za wagę'
    ) {
        $this->maxWeightWithoutSurcharge = Number::create($maxWeightWithoutSurcharge);
        $this->surchargePerKg = Number::create($surchargePerKg);
    }

    public function isApplicable(OrderInterface $order, DeliveryCostInfoInterface $deliveryCost): bool
    {
        return $order->getWeight()->toFloat() > $this->maxWeightWithoutSurcharge->toFloat()
            && $deliveryCost->getCurrencyCode() === $this->currencyCode;
    }

    public function calculate(OrderInterface $order, DeliveryCostInterface $deliveryCost): void
    {
        $cost = $order->getWeight()
            ->sub($this->maxWeightWithoutSurcharge)
            ->ceil()
            ->multiply($this->surchargePerKg);

        $deliveryCost->add($cost);
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
