<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use App\Factory\OrderItemFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class OrderItemFactoryTest extends TestCase
{

    public static function createDataProvider(): array
    {
        return [
            'valid data' => [
                [
                    'position' => 1,
                    'name' => 'Test Item',
                    'price' => 10.5,
                    'currency' => 'USD',
                    'quantity' => 2,
                ],
                [
                    'position' => 1,
                    'name' => 'Test Item',
                    'price' => '10.50',
                    'currency' => 'USD',
                    'quantity' => 2,
                ]
            ],
            'with default values' => [
                [

                ],
                [
                    'position' => 1,
                    'name' => '',
                    'price' => '0.00',
                    'currency' => 'PLN',
                    'quantity' => 0,
                ]
            ],
        ];
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(array $data, array $expected): void
    {
        $factory = new OrderItemFactory();

        $item = $factory->create($data);

        $this->assertSame($expected['position'], $item->getPosition());
        $this->assertSame($expected['name'], $item->getName());
        $this->assertSame($expected['price'], $item->getPrice()->getValue());
        $this->assertSame($expected['currency'], $item->getPrice()->getCurrency());
        $this->assertSame($expected['quantity'], $item->getQuantity());
    }
}
