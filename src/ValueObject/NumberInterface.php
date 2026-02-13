<?php

declare(strict_types=1);

namespace App\ValueObject;

use ValueError;

interface NumberInterface
{
    public function getValue(): string;
    public function toFloat(): float;
    /** @throws ValueError */
    public function add(string|int|float|NumberInterface $value): self;
    /** @throws ValueError */
    public function sub(string|int|float|NumberInterface $value): self;
    /** @throws ValueError */
    public function multiply(string|int|float|NumberInterface $value): self;
    public function floor(): self;
    public function ceil(): self;
}
