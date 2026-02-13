<?php

declare(strict_types=1);

namespace App\Model;

use App\ValueObject\NumberInterface;
use App\ValueObject\MoneyInterface;
use DateTimeImmutable;

interface OrderInterface
{
    public function getCountryCode(): string;
    public function getWeight(): NumberInterface;
    public function getTotalPrice(): MoneyInterface;
    public function getCreatedAt(): DateTimeImmutable;
}
