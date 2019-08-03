<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\IssueParserState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\SecondLineParserState;
use PHPUnit\Framework\TestCase;

class SecondLineParserStateTest extends TestCase
{
    private const LINE = '-------------------';

    /**
     * @var SecondLineParserState
     */
    private $secondLineParserState;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/foo', '/home');
        $this->secondLineParserState = new SecondLineParserState(new AnalysisResults(), new FileName('/foo'), $projectRoot);
    }

    public function testIsLine(): void
    {
        $nextLineParserState = $this->secondLineParserState->parseLine(self::LINE);
        $this->assertInstanceOf(IssueParserState::class, $nextLineParserState);
    }

    public function testNotFileName(): void
    {
        $this->expectException(InvalidFileFormatException::class);
        $this->secondLineParserState->parseLine('Some other text');
    }
}
