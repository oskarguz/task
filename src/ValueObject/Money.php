<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\ValueObject\Number;
use App\ValueObject\NumberInterface;
use InvalidArgumentException;
use ValueError;

readonly class Money implements MoneyInterface
{
    /**
     * @throws ValueError
     */
    public static function create(string|int|float|NumberInterface $value, string $currencyCode = 'PLN'): self
    {
        return new self(
            $value instanceof NumberInterface ? $value : Number::create($value),
            $currencyCode
        );
    }

    private function __construct(
        private readonly NumberInterface $value,
        private readonly string $currencyCode = 'PLN',
    ) {
    }

    public function getValue(): NumberInterface
    {
        return $this->value;
    }

    public function getValueScalar(): string
    {
        return $this->value->getValue();
    }

    public function getFormatted(): string
    {
        return $this->value->getValue() . ' ' . $this->getCurrencyCode();
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function add(MoneyInterface $price): self
    {
        if ($this->getCurrencyCode() !== $price->getCurrencyCode()) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->value->add($price->getValue()), $this->getCurrencyCode());
    }
}
