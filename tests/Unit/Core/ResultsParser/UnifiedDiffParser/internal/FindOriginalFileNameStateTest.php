<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FindNewFileNameState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FindOriginalFileNameState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FindRenameToState;
use PHPUnit\Framework\TestCase;

class FindOriginalFileNameStateTest extends TestCase
{
    /**
     * @var FindOriginalFileNameState
     */
    private $findOriginalFileNameState;

    protected function setUp(): void
    {
        $fileMutationBuilder = new FileMutationsBuilder();
        $this->findOriginalFileNameState = new FindOriginalFileNameState($fileMutationBuilder);
    }

    public function testNotOriginalFileNameStart(): void
    {
        $newState = $this->findOriginalFileNameState->processLine('similarity index 100%');
        $this->assertSame($this->findOriginalFileNameState, $newState);
    }

    public function testOriginalFileNameStart(): void
    {
        $newState = $this->findOriginalFileNameState->processLine(SampleDiffLines::ORIGINAL_FILE_NAME);
        $this->assertInstanceOf(FindNewFileNameState::class, $newState);
    }

    public function testRenameFromFound(): void
    {
        $newState = $this->findOriginalFileNameState->processLine(SampleDiffLines::RENAME_FROM);
        $this->assertInstanceOf(FindRenameToState::class, $newState);
    }
}
