<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use PHPUnit\Framework\TestCase;

abstract class AbstractOutputFormatterTest extends TestCase
{
    private const FILE_1 = '/FILE_1';
    private const FILE_2 = '/FILE_2';
    private const TYPE_1 = 'TYPE_1';
    private const TYPE_2 = 'TYPE_2';

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
        $this->addAnalysisResult(
            $analysisResultsBuilder,
            self::FILE_1,
            10,
            self::TYPE_1,
            'MESSAGE_1',
            [
                'column' => '10',
            ],
            Severity::error(),
        );
        $this->addAnalysisResult(
            $analysisResultsBuilder,
            self::FILE_1,
            12,
            self::TYPE_2,
            'MESSAGE_2',
            [
                'column' => 'invalid',
            ],
            Severity::error(),
        );
        $this->addAnalysisResult(
            $analysisResultsBuilder,
            self::FILE_2,
            0,
            self::TYPE_1,
            'MESSAGE_3',
            [],
            Severity::warning()
        );

        $this->assertOutput($expectedOutput, $analysisResultsBuilder->build());
    }

    private function assertOutput(string $expectedOutput, AnalysisResults $analysisResults): void
    {
        $outputFormatter = $this->getOutputFormatter();
        $output = $outputFormatter->outputResults($analysisResults);
        $this->assertSame($expectedOutput, $output);
    }

    /** @param array<mixed> $data */
    private function addAnalysisResult(
        AnalysisResultsBuilder $analysisResultsBuilder,
        string $file,
        int $lineNumberAsInt,
        string $type,
        string $message,
        array $data,
        Severity $severity
    ): void {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $absoluteFileName = new AbsoluteFileName($file);
        $lineNumber = new LineNumber($lineNumberAsInt);
        $location = Location::fromAbsoluteFileName($absoluteFileName, $projectRoot, $lineNumber);

        $analysisResult = new AnalysisResult($location, new Type($type), $message, $data, $severity);

        $analysisResultsBuilder->addAnalysisResult($analysisResult);
    }
}
