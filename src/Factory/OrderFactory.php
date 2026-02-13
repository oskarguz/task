<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Order;
use DateTimeImmutable;
use App\ValueObject\Money;
use App\ValueObject\Number;
use App\Enum\OrderFields;
use ValueError;

class OrderFactory
{
    private const string DEFAULT_WEIGHT = '0';
    private const string DEFAULT_PRICE = '0';
    private const string DEFAULT_CURRENCY = 'PLN';
    private const string DEFAULT_COUNTRY_CODE = 'PL';
    private const string DEFAULT_CREATED_AT = '@0';
    private const string DATETIME_FORMAT = DateTimeImmutable::ATOM;
    private const array DEFAULT_ITEMS = [];

    public function __construct(
        private readonly OrderItemFactory $orderItemFactory
    ) {
    }

    /**
     * @throws ValueError
     */
    public function create(array $data): Order
    {
        $createdAt = DateTimeImmutable::createFromFormat(
            self::DATETIME_FORMAT,
            (string) ($data[OrderFields::createdAt->name] ?? '')
        );
        if ($createdAt === false) {
            $createdAt = new DateTimeImmutable(self::DEFAULT_CREATED_AT);
        }

        return new Order(
            weight: Number::create((string) ($data[OrderFields::weight->name] ?? self::DEFAULT_WEIGHT)),
            totalPrice: Money::create(
                (string) ($data[OrderFields::totalPrice->name] ?? self::DEFAULT_PRICE),
                (string) ($data[OrderFields::currency->name] ?? self::DEFAULT_CURRENCY)
            ),
            countryCode: (string) ($data[OrderFields::countryCode->name] ?? self::DEFAULT_COUNTRY_CODE),
            createdAt: $createdAt,
            items: array_map(
                fn($itemData) => $this->orderItemFactory->create($itemData),
                $data[OrderFields::items->name] ?? self::DEFAULT_ITEMS
            )
        );
    }
}
