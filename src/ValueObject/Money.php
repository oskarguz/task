<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\ValueObject\Number;
use App\ValueObject\NumberInterface;
use InvalidArgumentException;
use ValueError;

readonly class Money implements NumberInterface
{
    /**
     * @throws ValueError
     */
    public static function create(string|int|float|NumberInterface $value, string $currency = 'PLN'): self
    {
        return new self($value instanceof NumberInterface ? $value : Number::create($value), $currency);
    }

    private function __construct(
        private readonly Number $value,
        private readonly string $currency = 'PLN',
    ) {
    }

    public function getValue(): string
    {
        return $this->value->getValue();
    }

    public function getFormatted(): string
    {
        return $this->value->getValue() . ' ' . $this->currency;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function add(Money $price): self
    {
        if ($this->currency !== $price->currency) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->value->add($price->getValue()), $this->currency);
    }
}
