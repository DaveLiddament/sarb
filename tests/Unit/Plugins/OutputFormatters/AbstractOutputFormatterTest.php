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
        $analysisResults = new AnalysisResults();

        $this->assertOutput($expectedOutput, $analysisResults);
    }

    protected function assertIssuesOutput(string $expectedOutput): void
    {
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

        $this->assertOutput($expectedOutput, $analysisResults);
    }

    private function assertOutput(string $expectedOutput, AnalysisResults $analysisResults): void
    {
        $outputFormatter = $this->getOutputFormatter();
        $output = $outputFormatter->outputResults($analysisResults);
        $this->assertSame($expectedOutput, $output);
    }
}
