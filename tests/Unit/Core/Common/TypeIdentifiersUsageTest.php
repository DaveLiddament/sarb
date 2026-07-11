<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\TypeIdentifiersUsage;
use PHPUnit\Framework\TestCase;

final class TypeIdentifiersUsageTest extends TestCase
{
    public function testNone(): void
    {
        $usage = TypeIdentifiersUsage::none();
        $this->assertNull($usage->asStringOrNull());
        $this->assertFalse($usage->isFromToolIdentifiers());
        $this->assertFalse($usage->isAllFromToolIdentifiers());
    }

    public function testSome(): void
    {
        $usage = TypeIdentifiersUsage::some();
        $this->assertSame('some', $usage->asStringOrNull());
        $this->assertTrue($usage->isFromToolIdentifiers());
        $this->assertFalse($usage->isAllFromToolIdentifiers());
    }

    public function testAll(): void
    {
        $usage = TypeIdentifiersUsage::all();
        $this->assertSame('all', $usage->asStringOrNull());
        $this->assertTrue($usage->isFromToolIdentifiers());
        $this->assertTrue($usage->isAllFromToolIdentifiers());
    }

    public function testFromNull(): void
    {
        $usage = TypeIdentifiersUsage::fromStringOrNull(null);
        $this->assertNull($usage->asStringOrNull());
        $this->assertFalse($usage->isFromToolIdentifiers());
    }

    public function testFromAll(): void
    {
        $usage = TypeIdentifiersUsage::fromStringOrNull('all');
        $this->assertSame('all', $usage->asStringOrNull());
        $this->assertTrue($usage->isAllFromToolIdentifiers());
    }

    public function testFromSome(): void
    {
        $usage = TypeIdentifiersUsage::fromStringOrNull('some');
        $this->assertSame('some', $usage->asStringOrNull());
        $this->assertTrue($usage->isFromToolIdentifiers());
        $this->assertFalse($usage->isAllFromToolIdentifiers());
    }

    public function testUnrecognisedStringTreatedAsSome(): void
    {
        $usage = TypeIdentifiersUsage::fromStringOrNull('rubbish');
        $this->assertSame('some', $usage->asStringOrNull());
        $this->assertTrue($usage->isFromToolIdentifiers());
        $this->assertFalse($usage->isAllFromToolIdentifiers());
    }
}
