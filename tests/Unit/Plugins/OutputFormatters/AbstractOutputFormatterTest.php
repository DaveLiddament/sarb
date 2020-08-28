<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use PHPUnit\Framework\TestCase;

abstract class AbstractOutputFormatterTest extends TestCase
{
    abstract protected function getOutputFormatter(): OutputFormatter;

    protected function assertName(string $expectedName): void
    {
        $outputFormatter = $this->getOutputFormatter();
        $this->assertSame($expectedName, $outputFormatter->getIdentifier());
    }

    protected function assertNoIssuesOutput(string $expectedOutput): void
    {
        $analysisResults = new AnalysisResults([]);

        $this->assertOutput($expectedOutput, $analysisResults);
    }

    protected function assertIssuesOutput(string $expectedOutput): void
    {
        $analysisResultsBuilder = new AnalysisResultsBuilder();
        $analysisResultsBuilder->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_1'), new LineNumber(10)),
            new Type('TYPE_1'),
            'MESSAGE_1',
            ''
        ));
        $analysisResultsBuilder->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_1'), new LineNumber(12)),
            new Type('TYPE_2'),
            'MESSAGE_2',
            ''
        ));
        $analysisResultsBuilder->addAnalysisResult(new AnalysisResult(
            new Location(new FileName('FILE_2'), new LineNumber(0)),
            new Type('TYPE_1'),
            'MESSAGE_3',
            ''
        ));

        $this->assertOutput($expectedOutput, $analysisResultsBuilder->build());
    }

    private function assertOutput(string $expectedOutput, AnalysisResults $analysisResults): void
    {
        $outputFormatter = $this->getOutputFormatter();
        $output = $outputFormatter->outputResults($analysisResults);
        $this->assertSame($expectedOutput, $output);
    }
}
