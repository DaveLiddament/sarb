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
    ];

    private const NAME_KEY = 'name';
    private const AGE_KEY = 'age';
    private const ADDRESS_KEY = 'address';
    private const NAME_VALUE = 'dave';
    private const AGE_VALUE = 21;
    private const ADDRESS_VALUE = [
        'some street',
        'some town',
    ];

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
}
