<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FirstLineParserState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\SummaryLineParserState;
use PHPUnit\Framework\TestCase;

class FirstLineParserStateTest extends TestCase
{
    private const LINE = '-------------------';

    /**
     * @var FirstLineParserState
     */
    private $firstLineParserState;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/foo', '/home');
        $this->firstLineParserState = new FirstLineParserState(new AnalysisResults(), new FileName('/foo'), $projectRoot);
    }

    public function testIsLine(): void
    {
        $nextLineParserState = $this->firstLineParserState->parseLine(self::LINE);
        $this->assertInstanceOf(SummaryLineParserState::class, $nextLineParserState);
    }

    public function testNotFileName(): void
    {
        $this->expectException(InvalidFileFormatException::class);
        $this->firstLineParserState->parseLine('Some other text');
    }
}
