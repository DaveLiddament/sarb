<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisBaseliner\Core\Utils\StringUtils;
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
}
