<?php

declare(strict_types=1);

namespace App\Service\DeliveryCostRule;

use App\Model\DeliveryCostInterface;
use App\Model\DeliveryCostInfoInterface;
use App\Model\OrderInterface;
use App\ValueObject\Number;
use App\ValueObject\NumberInterface;

class BaseRateByCountry implements DeliveryCostRuleInterface
{
    private NumberInterface $defaultBaseRate;
    /** @var array<string, NumberInterface> */
    private array $baseRatesPerCountry;

    public function __construct(
        string $defaultBaseRate = '39.99',
        array $baseRatesPerCountry = [
            'PL' => '10.00',
            'DE' => '20.00',
            'USA' => '50.00',
        ],
        private readonly string $currencyCode = 'PLN',
        private readonly int $priority = 100,
        private readonly string $label = 'Stawki bazowe według kraju'
    ) {
        $this->defaultBaseRate = Number::create($defaultBaseRate);
        $this->baseRatesPerCountry = array_map([Number::class, 'create'], $baseRatesPerCountry);
    }

    public function isApplicable(OrderInterface $order, DeliveryCostInfoInterface $deliveryCost): bool
    {
        return $this->currencyCode === $deliveryCost->getCurrencyCode();
    }

    public function calculate(OrderInterface $order, DeliveryCostInterface $deliveryCost): void
    {
        $countryCode = $order->getCountryCode();
        $baseRate = $this->baseRatesPerCountry[$countryCode] ?? $this->defaultBaseRate;
        $deliveryCost->add($baseRate);
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
