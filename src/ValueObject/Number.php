<?php

declare(strict_types=1);

namespace App\ValueObject;

use ValueError;

readonly class Number implements NumberInterface
{
    /**
     * @throws ValueError
     */
    public static function create(string|int|float|NumberInterface $value): self
    {
        $val = (string)($value instanceof NumberInterface ? $value->getValue() : $value);

        return new self(bcround($val, 2));
    }

    private function __construct(
        private readonly string $value
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @throws ValueError
     */
    public function add(string|int|float|NumberInterface $value): self
    {
        if ($value instanceof NumberInterface) {
            $val = $value->getValue();
        } else {
            $val = Number::create($value)->getValue();
        }

        $sum = bcadd($this->value, $val, 2);

        return new self($sum);
    }
}
