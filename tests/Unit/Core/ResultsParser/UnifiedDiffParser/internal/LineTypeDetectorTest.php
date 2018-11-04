<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\LineTypeDetector;
use PHPUnit\Framework\TestCase;

class LineTypeDetectorTest extends TestCase
{
    public function testMatchStartDiff(): void
    {
        $this->assertTrue(LineTypeDetector::isStartOfFileDiff(SampleDiffLines::DIFF_START));
    }

    public function testNoMatchStartDiff(): void
    {
        $this->assertFalse(LineTypeDetector::isStartOfFileDiff('--- a/foo.php'));
    }

    public function testMatchStartOfChangeHunk(): void
    {
        $this->assertTrue(LineTypeDetector::isStartOfChangeHunk(SampleDiffLines::CHANGE_HUNK_START));
    }

    public function testNoMatchStartOfChangeHunk(): void
    {
        $this->assertFalse(LineTypeDetector::isStartOfChangeHunk('     private $name;'));
    }
}
