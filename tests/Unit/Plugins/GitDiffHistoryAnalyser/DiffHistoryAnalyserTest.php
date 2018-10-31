<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
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

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
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
        $location = $this->createLocation($fileName, $lineNumber);

        return $diffHistoryAnalyser->getPreviousLocation($location);
    }

    private function createLocation(string $fileName, int $lineNumber): Location
    {
        return new Location(new FileName($fileName), new LineNumber($lineNumber));
    }

    private function assertPreviousLocation(string $fileName, int $lineNumber, PreviousLocation $previousLocation): void
    {
        $this->assertFalse($previousLocation->isNoPreviousLocation());
        $expectedLocation = $this->createLocation($fileName, $lineNumber);
        $this->assertTrue($expectedLocation->isEqual($previousLocation->getLocation()));
    }

    private function assertNoPreviousLocation(PreviousLocation $previousLocation): void
    {
        $this->assertTrue($previousLocation->isNoPreviousLocation());
    }
}
