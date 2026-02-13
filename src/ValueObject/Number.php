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

    public function toFloat(): float
    {
        return (float) $this->value;
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

    /**
     * @throws ValueError
     */
    public function sub(string|int|float|NumberInterface $value): self
    {
        if ($value instanceof NumberInterface) {
            $val = $value->getValue();
        } else {
            $val = Number::create($value)->getValue();
        }

        $result = bcsub($this->value, $val, 2);

        return new self($result);
    }

    /**
     * @throws ValueError
     */
    public function multiply(string|int|float|NumberInterface $value): self
    {
        if ($value instanceof NumberInterface) {
            $val = $value->getValue();
        } else {
            $val = Number::create($value)->getValue();
        }

        $result = bcmul($this->value, $val, 2);

        return new self($result);
    }

    public function floor(): self
    {
        $floored = bcfloor($this->value);

        return self::create($floored);
    }

    public function ceil(): self
    {
        $ceiled = bcceil($this->value);

        return self::create($ceiled);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
