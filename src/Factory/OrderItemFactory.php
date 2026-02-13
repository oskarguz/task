<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\OrderItem;
use App\ValueObject\Money;
use App\Enum\OrderItemFields;
use ValueError;

class OrderItemFactory
{
    private const int DEFAULT_POSITION = 1;
    private const string DEFAULT_NAME = '';
    private const string DEFAULT_PRICE = '0';
    private const string DEFAULT_CURRENCY = 'PLN';
    private const int DEFAULT_QUANTITY = 0;

    /**
     * @throws ValueError
     */
    public function create(array $data): OrderItem
    {
        return new OrderItem(
            (int) ($data[OrderItemFields::position->name] ?? self::DEFAULT_POSITION),
            (string) ($data[OrderItemFields::name->name] ?? self::DEFAULT_NAME),
            Money::create(
                (string) ($data[OrderItemFields::price->name] ?? self::DEFAULT_PRICE),
                (string) ($data[OrderItemFields::currency->name] ?? self::DEFAULT_CURRENCY)
            ),
            (int) ($data[OrderItemFields::quantity->name] ?? self::DEFAULT_QUANTITY)
        );
    }
}
