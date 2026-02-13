<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\DeliveryCost;
use App\Model\DeliveryCostInterface;
use App\Model\OrderInterface;
use App\Service\DeliveryCostRule\DeliveryCostRuleInterface;
use App\ValueObject\Money;

class CalculateDeliveryCost
{
    /** @var iterable<DeliveryCostRuleInterface> */
    private readonly iterable $deliveryCostRuleCollection;

    public function __construct(
        iterable $deliveryCostRuleCollection,
    ) {
        $this->deliveryCostRuleCollection = $this->sortRulesByPriority($deliveryCostRuleCollection);
    }

    public function execute(OrderInterface $order): DeliveryCostInterface
    {
        $deliveryCost = new DeliveryCost(Money::create(0));
        foreach ($this->deliveryCostRuleCollection as $rule) {
            if ($rule->isApplicable($order, $deliveryCost)) {
                $rule->calculate($order, $deliveryCost);
            }
        }
        return $deliveryCost;
    }

    private function sortRulesByPriority(iterable $rules): iterable
    {
        $rules = is_array($rules) ? $rules : iterator_to_array($rules);
        usort(
            $rules,
            fn(DeliveryCostRuleInterface $a, DeliveryCostRuleInterface $b) => $a->priority() <=> $b->priority()
        );

        return $rules;
    }
}
