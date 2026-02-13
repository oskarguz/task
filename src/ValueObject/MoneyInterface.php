<?php

declare(strict_types=1);

namespace App\ValueObject;

interface MoneyInterface
{
    public function getValue(): NumberInterface;
    public function getValueScalar(): string;
    public function getCurrencyCode(): string;
    public function getFormatted(): string;
}
