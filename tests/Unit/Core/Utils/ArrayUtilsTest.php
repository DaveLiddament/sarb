<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use PHPUnit\Framework\TestCase;

class ArrayUtilsTest extends TestCase
{
    private const TEST_ARRAY = [
        self::NAME_KEY => self::NAME_VALUE,
        self::AGE_KEY => self::AGE_VALUE,
        self::ADDRESS_KEY => self::ADDRESS_VALUE,
        self::INT_AS_STRING_KEY => self::INT_AS_STRING_VALUE,
        self::NULL_KEY => null,
    ];

    private const NULL_KEY = 'null';
    private const NAME_KEY = 'name';
    private const AGE_KEY = 'age';
    private const ADDRESS_KEY = 'address';
    private const NAME_VALUE = 'dave';
    private const AGE_VALUE = 21;
    private const INT_AS_STRING_KEY = 'number';
    private const INT_AS_STRING_VALUE = '31';
    private const INT_AS_INT_VALUE = 31;
    private const ADDRESS_VALUE = [
        'some street',
        'some town',
    ];
    private const INVALID_KEY = 'foo';

    public function testGetStringValue(): void
    {
        $actual = ArrayUtils::getStringValue(self::TEST_ARRAY, self::NAME_KEY);
        $this->assertSame(self::NAME_VALUE, $actual);
    }

    public function testGetIntValue(): void
    {
        $actual = ArrayUtils::getIntValue(self::TEST_ARRAY, self::AGE_KEY);
        $this->assertSame(self::AGE_VALUE, $actual);
    }

    public function testGetArrayValue(): void
    {
        $actual = ArrayUtils::getArrayValue(self::TEST_ARRAY, self::ADDRESS_KEY);
        $this->assertEquals(self::ADDRESS_VALUE, $actual);
    }

    public function testGetIntAsString(): void
    {
        $actual = ArrayUtils::getIntAsStringValue(self::TEST_ARRAY, self::INT_AS_STRING_KEY);
        $this->assertEquals(self::INT_AS_INT_VALUE, $actual);
    }

    public function testGetStringOnNoneString(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getStringValue(self::TEST_ARRAY, self::AGE_KEY);
    }

    public function testGetIntOnNoneString(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getIntValue(self::TEST_ARRAY, self::NAME_KEY);
    }

    public function testGetArrayOnNoneString(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getArrayValue(self::TEST_ARRAY, self::AGE_KEY);
    }

    public function testGetIntOrNullWithInt(): void
    {
        $actual = ArrayUtils::getIntOrNullValue(self::TEST_ARRAY, self::AGE_KEY);
        $this->assertEquals(self::AGE_VALUE, $actual);
    }

    public function testGetIntOrNullWithNull(): void
    {
        $actual = ArrayUtils::getIntOrNullValue(self::TEST_ARRAY, self::NULL_KEY);
        $this->assertNull($actual);
    }

    public function testGetIntOrNullWithString(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getIntOrNullValue(self::TEST_ARRAY, self::NAME_KEY);
    }

    public function testGetInAsAsStringNoneString(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getIntAsStringValue(self::TEST_ARRAY, self::AGE_KEY);
    }

    public function testGetInAsAsStringKeyMissing(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getIntAsStringValue(self::TEST_ARRAY, self::INVALID_KEY);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAssertArrayWithArray(): void
    {
        ArrayUtils::assertArray(self::TEST_ARRAY);
    }

    public function testAssertArrayOnNoneArray(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::assertArray(self::AGE_VALUE);
    }

    public function testInvalidArrayKey(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getStringValue(self::TEST_ARRAY, self::INVALID_KEY);
    }

    public function testGetOptionalStringWithStringValue(): void
    {
        $actual = ArrayUtils::getOptionalStringValue(self::TEST_ARRAY, self::NAME_KEY);
        $this->assertEquals(self::NAME_VALUE, $actual);
    }

    public function testGetOptionalStringWithNoKey(): void
    {
        $actual = ArrayUtils::getOptionalStringValue(self::TEST_ARRAY, self::INVALID_KEY);
        $this->assertNull($actual);
    }

    public function testGetOptionalStringWithIncorrectType(): void
    {
        $this->expectException(ArrayParseException::class);
        ArrayUtils::getOptionalStringValue(self::TEST_ARRAY, self::AGE_KEY);
    }
}
