<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\DiffParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FindChangeHunkStartState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FindNewFileNameState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\OriginalFileName;
use PHPUnit\Framework\TestCase;

class FindNewFileNameStateTest extends TestCase
{
    /**
     * @var OriginalFileName
     */
    private $originalFileName;

    /**
     * @var FindNewFileNameState
     */
    private $findNewFileNameState;

    /**
     * @var FileMutationsBuilder
     */
    private $fileMutationsBuilder;

    protected function setUp(): void
    {
        $this->originalFileName = new OriginalFileName('src/Person.php');

        $this->fileMutationsBuilder = new FileMutationsBuilder();
        $fileMutationBuilder = new FileMutationBuilder($this->fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName($this->originalFileName);
        $this->findNewFileNameState = new FindNewFileNameState($fileMutationBuilder);
    }

    public function testNotNewFileName(): void
    {
        $this->expectException(DiffParseException::class);
        $this->findNewFileNameState->processLine('similarity index 100%');
    }

    public function testNewFileName(): void
    {
        $newState = $this->findNewFileNameState->processLine(SampleDiffLines::NEW_FILE_NAME);
        $this->assertInstanceOf(FindChangeHunkStartState::class, $newState);
        $newState->finish();

        $newFileName = new NewFileName('src/Student.php');

        $fileMutations = $this->fileMutationsBuilder->build();
        $fileMutation = $fileMutations->getFileMutation($newFileName);
        $this->assertNotNull($fileMutation);
        $this->assertFalse($fileMutation->isDeletedFile());
        $this->assertFalse($fileMutation->isAddedFile());

        $this->assertEquals($this->originalFileName, $fileMutation->getOriginalFileName());
        $this->assertEquals($newFileName, $fileMutation->getNewFileName());
        $this->assertCount(0, $fileMutation->getLineMutations());
    }
}
