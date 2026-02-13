<?php

declare(strict_types=1);

namespace App\Model;

use App\ValueObject\MoneyInterface;

class OrderItem
{
    public function __construct(
        private int $position,
        private string $name,
        private MoneyInterface $price,
        private int $quantity
    ) {
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): MoneyInterface
    {
        return $this->price;
    }

    public function setPrice(MoneyInterface $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}
