<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

final class StringUtilsTest extends TestCase
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
        $this->expectException(\InvalidArgumentException::class);
        StringUtils::removeFromStart('Bar', 'FooBar');
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
