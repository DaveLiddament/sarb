<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use PHPUnit\Framework\TestCase;

class DiffHistoryAnalyserTest extends TestCase
{
    private const FILE_1 = 'foo/one.txt';
    private const FILE_2 = 'bar/two.txt';
    private const FILE_3 = 'bar/three.txt';

    /**
     * @var FileMutationsBuilder
     */
    private $fileMutationsBuilder;

    protected function setUp(): void
    {
        $this->fileMutationsBuilder = new FileMutationsBuilder();
    }

    public function testNoFileMutations(): void
    {
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 1);
        $this->assertPreviousLocation(self::FILE_1, 1, $previousLocation);
    }

    public function testFileAdded(): void
    {
        $this->addFile(self::FILE_1);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 1);
        $this->assertNoPreviousLocation($previousLocation);
    }

    public function testLineAdded(): void
    {
        $this->addLine(self::FILE_1, 1);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 1);
        $this->assertNoPreviousLocation($previousLocation);
    }

    public function testLineAddedBefore(): void
    {
        $this->addLine(self::FILE_1, 1);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 2);
        $this->assertPreviousLocation(self::FILE_1, 1, $previousLocation);
    }

    public function testLineRemovedBefore(): void
    {
        $this->removeLine(self::FILE_1, 2);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 2);
        $this->assertPreviousLocation(self::FILE_1, 3, $previousLocation);
    }

    public function testLineRemovedAfter(): void
    {
        $this->removeLine(self::FILE_1, 5);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 2);
        $this->assertPreviousLocation(self::FILE_1, 2, $previousLocation);
    }

    public function testLineAddedAfter(): void
    {
        $this->addLine(self::FILE_1, 5);
        $previousLocation = $this->getPreviousLocation(self::FILE_1, 2);
        $this->assertPreviousLocation(self::FILE_1, 2, $previousLocation);
    }

    public function testLineAddedBeforeAndFileNameChanged(): void
    {
        $this->renameAndAddLine(self::FILE_1, self::FILE_2, 1);
        $previousLocation = $this->getPreviousLocation(self::FILE_2, 2);
        $this->assertPreviousLocation(self::FILE_1, 1, $previousLocation);
    }

    public function addFile(string $fileName): void
    {
        $fileMutationBuilder = new FileMutationBuilder($this->fileMutationsBuilder);
        $fileMutationBuilder->setNewFileName(new NewFileName($fileName));
        $fileMutationBuilder->build();
    }

    public function addLine(string $fileName, int $lineNumber): void
    {
        $fileMutationBuilder = new FileMutationBuilder($this->fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName(new OriginalFileName($fileName));
        $fileMutationBuilder->setNewFileName(new NewFileName($fileName));
        $fileMutationBuilder->addLineMutation(LineMutation::newLineNumber(new LineNumber($lineNumber)));
        $fileMutationBuilder->build();
    }

    public function removeLine(string $fileName, int $lineNumber): void
    {
        $fileMutationBuilder = new FileMutationBuilder($this->fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName(new OriginalFileName($fileName));
        $fileMutationBuilder->setNewFileName(new NewFileName($fileName));
        $fileMutationBuilder->addLineMutation(LineMutation::originalLineNumber(new LineNumber($lineNumber)));
        $fileMutationBuilder->build();
    }

    public function renameAndAddLine(string $originalFileName, string $newFileName, int $lineNumber): void
    {
        $fileMutationBuilder = new FileMutationBuilder($this->fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName(new OriginalFileName($originalFileName));
        $fileMutationBuilder->setNewFileName(new NewFileName($newFileName));
        $fileMutationBuilder->addLineMutation(LineMutation::newLineNumber(new LineNumber($lineNumber)));
        $fileMutationBuilder->build();
    }

    private function getPreviousLocation(string $fileName, int $lineNumber): PreviousLocation
    {
        $fileMutations = $this->fileMutationsBuilder->build();
        $diffHistoryAnalyser = new DiffHistoryAnalyser($fileMutations);

        return $diffHistoryAnalyser->getPreviousLocation(
            new FileName($fileName),
            new LineNumber($lineNumber)
        );
    }

    private function assertPreviousLocation(string $fileName, int $lineNumber, PreviousLocation $previousLocation): void
    {
        $this->assertFalse($previousLocation->isNoPreviousLocation());
        $this->assertSame($fileName, $previousLocation->getFileName()->getFileName());
        $this->assertSame($lineNumber, $previousLocation->getLineNumber()->getLineNumber());
    }

    private function assertNoPreviousLocation(PreviousLocation $previousLocation): void
    {
        $this->assertTrue($previousLocation->isNoPreviousLocation());
    }
}
