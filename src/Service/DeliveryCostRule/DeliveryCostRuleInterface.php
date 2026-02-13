<?php

declare(strict_types=1);

namespace App\Service\DeliveryCostRule;

use App\Model\DeliveryCostInterface;
use App\Model\DeliveryCostInfoInterface;
use App\Model\OrderInterface;

interface DeliveryCostRuleInterface
{
    public function isApplicable(OrderInterface $order, DeliveryCostInfoInterface $deliveryCost): bool;
    public function calculate(OrderInterface $order, DeliveryCostInterface $deliveryCost): void;
    public function priority(): int;
    public function label(): string;
}
