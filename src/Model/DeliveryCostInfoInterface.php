<?php

declare(strict_types=1);

namespace App\Model;

use App\ValueObject\NumberInterface;

interface DeliveryCostInfoInterface
{
    public function getValue(): NumberInterface;
    public function getCurrencyCode(): string;
}
