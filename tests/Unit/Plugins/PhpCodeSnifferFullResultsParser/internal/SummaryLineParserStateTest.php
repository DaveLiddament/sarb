<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\SecondLineParserState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\SummaryLineParserState;
use PHPUnit\Framework\TestCase;

class SummaryLineParserStateTest extends TestCase
{
    private const VALID_LINE = 'FOUND 1 ERROR AND 1 WARNING AFFECTING 2 LINES';

    /**
     * @var SummaryLineParserState
     */
    private $summaryLineParserState;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/foo', '/home');
        $this->summaryLineParserState = new SummaryLineParserState(new AnalysisResults(), new FileName('/foo'), $projectRoot);
    }

    public function testIsLine(): void
    {
        $nextLineParserState = $this->summaryLineParserState->parseLine(self::VALID_LINE);
        $this->assertInstanceOf(SecondLineParserState::class, $nextLineParserState);
    }

    public function testNotFileName(): void
    {
        $this->expectException(InvalidFileFormatException::class);
        $this->summaryLineParserState->parseLine('Some other text');
    }
}
