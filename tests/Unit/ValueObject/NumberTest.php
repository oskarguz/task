<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\ValueObject\Number;
use ValueError;

class NumberTest extends TestCase
{
    public static function createDataProvider(): array
    {
        return [
            ['10', '10.00'],
            [10, '10.00'],
            [10.5, '10.50'],
            [10.5555, '10.56'],
            ['10.5555', '10.56'],
            ['-999.995', '-1000.00'],
            ['-999.994', '-999.99'],
            [-1, '-1.00'],
            [0, '0.00'],
            [0.0, '0.00'],
            ['0', '0.00'],
        ];
    }

    #[DataProvider('createDataProvider')]
    public function testCreate(mixed $value, string $expected): void
    {
        $actual = Number::create($value);
        $this->assertSame($expected, $actual->getValue());
    }

    public static function createWithInvalidValueDataProvider(): array
    {
        return [
            ['abc'],
            ['1,23.45'],
            ['10.567.89'],
            ['10 55'],
            ['aa10'],
            ['10aa']
        ];
    }

    #[DataProvider('createWithInvalidValueDataProvider')]
    public function testCreateWithInvalidValue(mixed $value): void
    {
        $this->expectException(ValueError::class);
        Number::create($value);
    }

    public static function addDataProvider(): array
    {
        return [
            ['10.00', '5.50', '15.50'],
            ['10.00', 4.25, '14.25'],
            ['10.00', 4.2555, '14.26'],
            ['10.00', 1, '11.00'],
            ['10.00', Number::create('0.25'), '10.25'],
            ['-10', 5, '-5.00'],
            ['-10', -5, '-15.00'],
            ['-10', '5.5555', '-4.44'],
        ];
    }

    #[DataProvider('addDataProvider')]
    public function testAdd(mixed $value1, mixed $value2, string $expected): void
    {
        $actual = Number::create($value1)->add($value2);
        $this->assertSame($expected, $actual->getValue());
    }
}
