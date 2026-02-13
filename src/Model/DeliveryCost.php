<?php

declare(strict_types=1);

namespace App\Model;

use App\ValueObject\Money;
use App\ValueObject\MoneyInterface;
use App\ValueObject\NumberInterface;

class DeliveryCost implements DeliveryCostInterface
{
    public function __construct(
        private MoneyInterface $value
    ) {
    }

    public function getValue(): NumberInterface
    {
        return $this->value->getValue();
    }

    public function getCurrencyCode(): string
    {
        return $this->value->getCurrencyCode();
    }

    public function add(NumberInterface $value): self
    {
        $result = $this->value->getValue()->add($value);
        $this->value = Money::create($result, $this->value->getCurrencyCode());

        return $this;
    }

    public function sub(NumberInterface $value): self
    {
        $result = $this->value->getValue()->sub($value);
        $this->value = Money::create($result, $this->value->getCurrencyCode());

        return $this;
    }

    public function multiply(NumberInterface $value): DeliveryCostInterface
    {
        $result = $this->value->getValue()->multiply($value);
        $this->value = Money::create($result, $this->value->getCurrencyCode());

        return $this;
    }
}
