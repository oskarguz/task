<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderItemFields
{
    case position;
    case name;
    case price;
    case currency;
    case quantity;
}
