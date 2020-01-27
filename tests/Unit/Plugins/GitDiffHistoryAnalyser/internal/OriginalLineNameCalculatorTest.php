<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\FileMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\OriginalLineNumberCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Tests code that works out what original line number was for the given new line number.
 *
 * Original file is this:
 * dave
 * alan
 * james
 * john
 * peter
 * jane
 *
 *
 * New file is this:
 * dave
 * alan
 * jack
 * peter
 * sally
 * jane
 * jason
 * rupert
 */
class OriginalLineNameCalculatorTest extends TestCase
{
    /**
     * @var FileMutation
     */
    private $fileMutation;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $originalFileName = new OriginalFileName('foo');
        $newFileName = new NewFileName('bar');
        $lineMutations = [
            LineMutation::originalLineNumber(new LineNumber(3)),
            LineMutation::originalLineNumber(new LineNumber(4)),
            LineMutation::newLineNumber(new LineNumber(3)),
            LineMutation::newLineNumber(new LineNumber(5)),
            LineMutation::newLineNumber(new LineNumber(7)),
        ];

        $this->fileMutation = new FileMutation($originalFileName, $newFileName, $lineMutations);
    }

    public function dataProvider(): array
    {
        return [
            'dave' => [1, 1],
            'alan' => [2, 2],
            'jack' => [3, null],
            'peter' => [4, 5],
            'sally' => [5, null],
            'jane' => [6, 6],
            'jason' => [7, null],
            'rupert' => [8, 7],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCalculateOriginalLine(int $newLineNumber, ?int $expectedOriginalLineNumber): void
    {
        $originalLineNumber = OriginalLineNumberCalculator::calculateOriginalLineNumber($this->fileMutation, $newLineNumber);
        $this->assertSame($expectedOriginalLineNumber, $originalLineNumber);
    }

    public function testLineNumber0(): void
    {
        $originalLineNumber = OriginalLineNumberCalculator::calculateOriginalLineNumber($this->fileMutation, 0);
        $this->assertSame(0, $originalLineNumber);
    }
}
