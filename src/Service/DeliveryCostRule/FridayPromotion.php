<?php

declare(strict_types=1);

namespace App\Service\DeliveryCostRule;

use App\Model\OrderInterface;
use App\ValueObject\Number;
use App\ValueObject\NumberInterface;
use App\Model\DeliveryCostInterface;
use App\Model\DeliveryCostInfoInterface;

class FridayPromotion implements DeliveryCostRuleInterface
{
    private const string FRIDAY = '5';

    private readonly NumberInterface $discount;

    public function __construct(
        string $discount = '0.5',
        private readonly string $applicableCurrencyCode = 'PLN',
        private readonly int $priority = 400,
        private readonly string $label = 'Promocyjne Piątki',
    ) {
        $this->discount = Number::create('1.00')->sub($discount);
    }

    public function isApplicable(OrderInterface $order, DeliveryCostInfoInterface $deliveryCost): bool
    {
        return $order->getCreatedAt()->format('N') === self::FRIDAY
            && $deliveryCost->getCurrencyCode() === $this->applicableCurrencyCode
            && $deliveryCost->getValue()->toFloat() > 0;
    }

    public function calculate(OrderInterface $order, DeliveryCostInterface $deliveryCost): void
    {
        $deliveryCost->multiply($this->discount);
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
