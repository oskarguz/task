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

    public static function subDataProvider(): array
    {
        return [
            ['10.00', '5.50', '4.50'],
            ['10.00', 4.25, '5.75'],
            ['10.00', 4.2555, '5.74'],
            ['10.00', 1, '9.00'],
            ['10.00', Number::create('0.25'), '9.75'],
            ['-10', 5, '-15.00'],
            ['-10', -5, '-5.00'],
            ['-10', '5.5555', '-15.56'],
        ];
    }

    #[DataProvider('subDataProvider')]
    public function testSub(mixed $value1, mixed $value2, string $expected): void
    {
        $actual = Number::create($value1)->sub($value2);
        $this->assertSame($expected, $actual->getValue());
    }

    public static function multiplyDataProvider(): array
    {
        return [
            ['10.00', '5.50', '55.00'],
            ['10.00', 4.25, '42.50'],
            ['10.00', 4.2555, '42.60'],
            ['10.00', 1, '10.00'],
            ['10.00', Number::create('0.25'), '2.50'],
            ['-10', 5, '-50.00'],
            ['-10', -5, '50.00'],
            ['-10', '5.5555', '-55.60'],
            ['-10', 0, '0.00'],
        ];
    }

    #[DataProvider('multiplyDataProvider')]
    public function testMultiply(mixed $value1, mixed $value2, string $expected): void
    {
        $actual = Number::create($value1)->multiply($value2);
        $this->assertSame($expected, $actual->getValue());
    }

    public static function floorDataProvider(): array
    {
        return [
            ['10.00', '10.00'],
            ['10.99', '10.00'],
            ['10.01', '10.00'],
            ['-10.99', '-11.00'],
            ['-10.01', '-11.00'],
            ['-10.00', '-10.00'],
        ];
    }

    #[DataProvider('floorDataProvider')]
    public function testFloor(mixed $value, string $expected): void
    {
        $actual = Number::create($value)->floor();
        $this->assertSame($expected, $actual->getValue());
    }

    public static function ceilDataProvider(): array
    {
        return [
            ['10.00', '10.00'],
            ['10.99', '11.00'],
            ['10.01', '11.00'],
            ['-10.99', '-10.00'],
            ['-10.01', '-10.00'],
            ['-10.00', '-10.00'],
        ];
    }

    #[DataProvider('ceilDataProvider')]
    public function testCeil(mixed $value, string $expected): void
    {
        $actual = Number::create($value)->ceil();
        $this->assertSame($expected, $actual->getValue());
    }
}
