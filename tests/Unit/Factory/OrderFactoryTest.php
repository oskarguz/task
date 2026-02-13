<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use App\Factory\OrderFactory;
use App\Model\OrderItem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Factory\OrderItemFactory;

class OrderFactoryTest extends TestCase
{
    public static function createDataProvider(): array
    {
        return [
            'valid data' => [
                [
                    'totalPrice' => 100.0,
                    'weight' => 10.0,
                    'currency' => 'USD',
                    'countryCode' => 'US',
                    'createdAt' => '2024-01-01T00:00:00+00:00',
                    'items' => [
                        [
                            'some random data'
                        ],
                        [
                            'some random data'
                        ],
                    ]
                ],
                [
                    'totalPrice' => '100.00',
                    'weight' => '10.00',
                    'currency' => 'USD',
                    'countryCode' => 'US',
                    'createdAt' => '2024-01-01 00:00:00',
                    'itemsCount' => 2
                ]
            ],
            'with default values' => [
                [],
                [
                    'totalPrice' => '0.00',
                    'weight' => '0.00',
                    'currency' => 'PLN',
                    'countryCode' => 'PL',
                    'createdAt' => '1970-01-01 00:00:00',
                    'itemsCount' => 0
                ]
            ],
        ];
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(array $data, array $expected): void
    {
        $orderItemFactory = $this->createStub(OrderItemFactory::class);
        $orderItemFactory
            ->method('create')
            ->willReturn($this->createStub(OrderItem::class));

        $factory = new OrderFactory($orderItemFactory);

        $order = $factory->create($data);

        $this->assertSame($expected['totalPrice'], $order->getTotalPrice()->getValueScalar());
        $this->assertSame($expected['weight'], $order->getWeight()->getValue());
        $this->assertSame($expected['currency'], $order->getTotalPrice()->getCurrencyCode());
        $this->assertSame($expected['countryCode'], $order->getCountryCode());
        $this->assertSame($expected['createdAt'], $order->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertCount($expected['itemsCount'], $order->getItems());
    }
}
