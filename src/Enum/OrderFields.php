<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderFields
{
    case totalPrice;
    case weight;
    case currency;
    case countryCode;
    case createdAt;
    case items;
}
