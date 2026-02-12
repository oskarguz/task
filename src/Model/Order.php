<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use App\ValueObject\Money;
use App\ValueObject\Number;

class Order
{
    public function __construct(
        private Number $weight,
        private Money $totalPrice,
        private string $countryCode,
        private DateTimeImmutable $createdAt,
        /** @var OrderItem[] */
        private array $items
    ) {
    }

    public function getWeight(): Number
    {
        return $this->weight;
    }

    public function setWeight(Number $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getTotalPrice(): Money
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(Money $totalPrice): self
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
