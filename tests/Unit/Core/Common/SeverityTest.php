<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use PHPUnit\Framework\TestCase;

final class SeverityTest extends TestCase
{
    public function testError(): void
    {
        $severity = Severity::error();
        $this->assertSame('error', $severity->getSeverity());
        $this->assertFalse($severity->isWarning());
    }

    public function testWarning(): void
    {
        $severity = Severity::warning();
        $this->assertSame('warning', $severity->getSeverity());
        $this->assertTrue($severity->isWarning());
    }

    public function testFromStringOrNullDefaultsToError(): void
    {
        $severity = Severity::fromStringOrNull(null);
        $this->assertSame('error', $severity->getSeverity());
    }

    public function testFromString(): void
    {
        $severity = Severity::fromStringOrNull('warning');
        $this->assertSame('warning', $severity->getSeverity());
    }

    public function testIsValueValid(): void
    {
        $this->assertTrue(Severity::isValueValid('error'));
        $this->assertTrue(Severity::isValueValid('warning'));
        $this->assertFalse(Severity::isValueValid('rubbish'));
    }
}
