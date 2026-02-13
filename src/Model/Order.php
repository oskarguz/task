<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use App\ValueObject\MoneyInterface;
use App\ValueObject\NumberInterface;

class Order implements OrderInterface
{
    public function __construct(
        private NumberInterface $weight, // in kilograms
        private MoneyInterface $totalPrice,
        private string $countryCode,
        private DateTimeImmutable $createdAt,
        /** @var OrderItem[] */
        private array $items
    ) {
    }

    public function getWeight(): NumberInterface
    {
        return $this->weight;
    }

    public function setWeight(NumberInterface $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getTotalPrice(): MoneyInterface
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(MoneyInterface $totalPrice): self
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }
}
