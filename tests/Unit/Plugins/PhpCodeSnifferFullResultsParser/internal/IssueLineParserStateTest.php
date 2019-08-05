<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FileNameLineParserState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\IssueParserState;
use PHPUnit\Framework\TestCase;

class IssueLineParserStateTest extends TestCase
{
    private const LINE = '--------------';
    private const FILENAME = '/foo/bar';
    private const RELATIVE_FILENAME = 'bar';

    /**
     * @var IssueParserState
     */
    private $issueParserState;
    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/foo', '/home');
        $this->analysisResults = new AnalysisResults();
        $this->issueParserState = new IssueParserState($this->analysisResults, new FileName(self::FILENAME), $projectRoot);
    }

    public function testIsLine(): void
    {
        $nextLineParserState = $this->issueParserState->parseLine(self::LINE);
        $this->assertInstanceOf(FileNameLineParserState::class, $nextLineParserState);
    }

    public function testInvalidLine(): void
    {
        $this->expectException(InvalidFileFormatException::class);
        $this->issueParserState->parseLine('Some other text');
    }

    public function testValidError(): void
    {
        $nextLineParserState = $this->issueParserState->parseLine('  2 | ERROR   | Missing file doc comment');
        $this->assertInstanceOf(IssueParserState::class, $nextLineParserState);
        $this->assertAnalysisResult(2, 'ERROR', 'Missing file doc comment', false);
    }

    public function testValidWarning(): void
    {
        $nextLineParserState = $this->issueParserState->parseLine('11 | WARNING | Line exceeds 85 characters; contains 109 characters');
        $this->assertInstanceOf(IssueParserState::class, $nextLineParserState);
        $this->assertAnalysisResult(11, 'WARNING', 'Line exceeds 85 characters; contains 109 characters', false);
    }

    public function testFixableError(): void
    {
        $nextLineParserState = $this->issueParserState->parseLine(' 52 | ERROR   | [x] Expected 18 spaces after parameter type; 1 found');
        $this->assertInstanceOf(IssueParserState::class, $nextLineParserState);
        $this->assertAnalysisResult(52, 'ERROR', 'Expected 18 spaces after parameter type; 1 found', true);
    }

    public function testNoneFixableWarning(): void
    {
        $nextLineParserState = $this->issueParserState->parseLine('  8 | WARNING | [ ] Line exceeds 85 characters; contains 113 characters');
        $this->assertInstanceOf(IssueParserState::class, $nextLineParserState);
        $this->assertAnalysisResult(8, 'WARNING', 'Line exceeds 85 characters; contains 113 characters', false);
    }

    private function assertAnalysisResult(int $lineNumber, string $level, string $type, $fixable): void
    {
        $results = $this->analysisResults->getAnalysisResults();
        $this->assertCount(1, $results);
        $result = $results[0];

        $expected = new Location(new FileName(self::RELATIVE_FILENAME), new LineNumber($lineNumber));
        $this->assertTrue($result->isMatch($expected, new Type($type)));
        $this->assertSame($result->getMessage(), $type);

        $fullDetails = JsonUtils::toArray($result->getFullDetails());
        $this->assertSame($level, $fullDetails['level']);
        $this->assertSame($fixable, $fullDetails['fixable']);
    }
}
