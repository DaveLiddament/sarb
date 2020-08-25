<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FindFileDiffStartState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FindOriginalFileNameState;
use PHPUnit\Framework\TestCase;

class FindFileDiffStartStateTest extends TestCase
{
    /**
     * @var FindFileDiffStartState
     */
    private $findFileDiffStartState;

    protected function setUp(): void
    {
        $fileMutationsBuilder = new FileMutationsBuilder();
        $this->findFileDiffStartState = new FindFileDiffStartState($fileMutationsBuilder);
    }

    public function testFindNotStartOfFile(): void
    {
        $newState = $this->findFileDiffStartState->processLine('Foo');
        $this->assertSame($this->findFileDiffStartState, $newState);
    }

    public function testFindStartOfFile(): void
    {
        $newState = $this->findFileDiffStartState->processLine(SampleDiffLines::DIFF_START);
        $this->assertInstanceOf(FindOriginalFileNameState::class, $newState);
    }
}
