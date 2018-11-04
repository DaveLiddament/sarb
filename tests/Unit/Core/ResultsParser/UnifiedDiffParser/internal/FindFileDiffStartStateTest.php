<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FindFileDiffStartState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FindOriginalFileNameState;
use PHPUnit\Framework\TestCase;

class FindFileDiffStartStateTest extends TestCase
{
    /**
     * @var FindFileDiffStartState
     */
    private $findFileDiffStartState;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
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
