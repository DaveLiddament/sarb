<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonIdentifier;
use PHPUnit\Framework\TestCase;

class SummaryStatsTest extends TestCase
{
    public function testHappyPath(): void
    {
        $identifier = new SarbJsonIdentifier();
        $summaryStats = new SummaryStats(1, 2, $identifier, 'git');
        $this->assertSame(1, $summaryStats->getLatestAnalysisResultsCount());
        $this->assertSame(2, $summaryStats->getBaseLineCount());
        $this->assertSame($identifier, $summaryStats->getResultsParser());
        $this->assertSame('git', $summaryStats->getHistoryAnalyserName());
    }
}
