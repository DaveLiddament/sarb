<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\UnifiedDiffParser\DiffParserTestSupport;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\OriginalFileName;

class ExpectedFileMutations
{
    /**
     * @var OriginalFileName
     */
    private $originalFileName;

    /**
     * @var NewFileName
     */
    private $newFileName;

    /**
     * @var bool
     */
    private $isAddedFile;

    /**
     * @var bool
     */
    private $isDeletedFile;

    /**
     * @var LineMutation[]
     */
    private $lineMutations;

    /**
     * @return ExpectedFileMutations
     */
    public static function addExpectedFileMutation(): self
    {
        return new self();
    }

    /**
     * ExpectedFileMutations constructor.
     */
    private function __construct()
    {
        $this->isAddedFile = false;
        $this->isDeletedFile = false;
        $this->lineMutations = [];
    }

    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = new OriginalFileName($originalFileName);

        return $this;
    }

    public function setNewFileName(string $newFileName): self
    {
        $this->newFileName = new NewFileName($newFileName);

        return $this;
    }

    public function added(): self
    {
        $this->isAddedFile = true;

        return $this;
    }

    public function deleted(): self
    {
        $this->isDeletedFile = true;

        return $this;
    }

    public function newLine(int $lineNumber): self
    {
        $this->lineMutations[] = LineMutation::newLineNumber(new LineNumber($lineNumber));

        return $this;
    }

    public function deleteLine(int $lineNumber): self
    {
        $this->lineMutations[] = LineMutation::originalLineNumber(new LineNumber($lineNumber));

        return $this;
    }

    public function getOriginalFileName(): OriginalFileName
    {
        return $this->originalFileName;
    }

    public function getNewFileName(): NewFileName
    {
        return $this->newFileName;
    }

    public function isAddedFile(): bool
    {
        return $this->isAddedFile;
    }

    public function isDeletedFile(): bool
    {
        return $this->isDeletedFile;
    }

    /**
     * @return LineMutation[]
     */
    public function getLineMutations(): array
    {
        return $this->lineMutations;
    }
}
