<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function testDoesStartWith(): void
    {
        $this->assertTrue(StringUtils::startsWith('Foo', 'FooBar'));
    }

    public function testDoesStartWith2(): void
    {
        $this->assertTrue(StringUtils::startsWith('Foo', 'FooBarFoo'));
    }

    public function testDoesNotStartWith(): void
    {
        $this->assertFalse(StringUtils::startsWith('oo', 'FooBar'));
    }

    public function testRemoveFromStart(): void
    {
        $this->assertEquals('Bar', StringUtils::removeFromStart('Foo', 'FooBar'));
    }

    public function testRemoveFromStartExceptionWhenInvalidStart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        StringUtils::removeFromStart('Bar', 'FooBar');
    }

    public function testDoesEndWith1(): void
    {
        $this->assertTrue(StringUtils::endsWith('e', 'abcde'));
    }

    public function testDoesEndWith2(): void
    {
        $this->assertTrue(StringUtils::endsWith('de', 'abcde'));
    }

    public function testDoesNotEndWith1(): void
    {
        $this->assertFalse(StringUtils::endsWith('d', 'abcde'));
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(StringUtils::isEmptyLine(''));
    }

    public function testIsEmptyWithWhiteSpaces(): void
    {
        $this->assertTrue(StringUtils::isEmptyLine('     '));
    }

    public function testIsNotEmpty(): void
    {
        $this->assertFalse(StringUtils::isEmptyLine('  a   '));
    }
}
