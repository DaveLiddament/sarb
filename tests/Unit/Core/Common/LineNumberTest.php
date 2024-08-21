<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use PHPUnit\Framework\TestCase;

final class LineNumberTest extends TestCase
{
    public function testHappyPath(): void
    {
        $lineNumber = new LineNumber(3);
        $this->assertSame(3, $lineNumber->getLineNumber());
    }

    public function testInvalidLineNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LineNumber(-1);
    }

    public function test0Allowed(): void
    {
        $lineNumber = new LineNumber(0);
        $this->assertSame(0, $lineNumber->getLineNumber());
    }

    public function testLineNumbersEqual(): void
    {
        $a = new LineNumber(2);
        $b = new LineNumber(2);
        $this->assertTrue($a->isEqual($b));
    }

    public function testLineNumbersNotEqual(): void
    {
        $a = new LineNumber(2);
        $b = new LineNumber(3);
        $this->assertFalse($a->isEqual($b));
    }
}
