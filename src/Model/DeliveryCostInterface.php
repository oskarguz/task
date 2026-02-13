<?php

declare(strict_types=1);

namespace App\Model;

use App\ValueObject\NumberInterface;

interface DeliveryCostInterface extends DeliveryCostInfoInterface
{
    public function add(NumberInterface $value): self;
    public function sub(NumberInterface $value): self;
    public function multiply(NumberInterface $value): self;
}
