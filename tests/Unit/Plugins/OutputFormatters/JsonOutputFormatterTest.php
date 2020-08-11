<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\JsonOutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonIdentifier;
use PHPUnit\Framework\TestCase;

class JsonOutputFormatterTest extends TestCase
{
    public function testName(): void
    {
        $jsonFormatter = new JsonOutputFormatter();
        $this->assertSame('json', $jsonFormatter->getName());
    }

    public function testNoIssues(): void
    {
        $summaryStats = new SummaryStats(2, 4, new SarbJsonIdentifier(), 'git');
        $analysisResults = new AnalysisResults();

        $jsonOutputFormatter = new JsonOutputFormatter();
        $output = $jsonOutputFormatter->outputResults($summaryStats, $analysisResults);

        $expectedOuput = <<<EOF
{
    "summary": {
        "latestAnalysisCount": 2,
        "baseLineCount": 4,
        "baseLineRemovedCount": 0
    },
    "issues": [],
    "success": true
}
EOF;

        $this->assertSame($expectedOuput, $output);
    }

    public function testWithIssues(): void
    {
        $summaryStats = new SummaryStats(2, 4, new SarbJsonIdentifier(), 'git');
        $analysisResults = new AnalysisResults();
        $analysisResults->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_1'), new LineNumber(10)),
            new Type('TYPE_1'),
            'MESSAGE_1',
            ''
        ));
        $analysisResults->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_1'), new LineNumber(12)),
            new Type('TYPE_2'),
            'MESSAGE_2',
            ''
        ));
        $analysisResults->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_2'), new LineNumber(0)),
            new Type('TYPE_1'),
            'MESSAGE_3',
            ''
        ));

        $jsonOutputFormatter = new JsonOutputFormatter();
        $output = $jsonOutputFormatter->outputResults($summaryStats, $analysisResults);

        $expectedOuput = <<<EOF
{
    "summary": {
        "latestAnalysisCount": 2,
        "baseLineCount": 4,
        "baseLineRemovedCount": 3
    },
    "issues": [
        {
            "file": "FILE_1",
            "line": 10,
            "type": "TYPE_1",
            "message": "MESSAGE_1"
        },
        {
            "file": "FILE_1",
            "line": 12,
            "type": "TYPE_2",
            "message": "MESSAGE_2"
        },
        {
            "file": "FILE_2",
            "line": 0,
            "type": "TYPE_1",
            "message": "MESSAGE_3"
        }
    ],
    "success": false
}
EOF;

        $this->assertSame($expectedOuput, $output);
    }
}
