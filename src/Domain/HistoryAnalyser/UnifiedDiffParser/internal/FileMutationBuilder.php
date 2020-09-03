<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\FileMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\OriginalFileName;
use Webmozart\Assert\Assert;

class FileMutationBuilder
{
    /**
     * @var FileMutationsBuilder
     */
    private $fileMutationsBuilder;

    /**
     * @var OriginalFileName|null
     */
    private $originalFileName;

    /**
     * @var NewFileName|null
     */
    private $newFileName;

    /**
     * @psalm-var list<LineMutation>
     *
     * @var LineMutation[]
     */
    private $lineMutations;

    /**
     * FileMutationBuilder constructor.
     */
    public function __construct(FileMutationsBuilder $fileMutationsBuilder)
    {
        $this->fileMutationsBuilder = $fileMutationsBuilder;
        $this->lineMutations = [];
        $this->newFileName = null;
        $this->originalFileName = null;
    }

    public function setOriginalFileName(OriginalFileName $originalFileName): void
    {
        Assert::null($this->originalFileName);
        $this->originalFileName = $originalFileName;
    }

    public function setNewFileName(NewFileName $newFileName): void
    {
        Assert::null($this->newFileName);
        $this->newFileName = $newFileName;
    }

    public function addLineMutation(LineMutation $lineMutation): void
    {
        $this->lineMutations[] = $lineMutation;
    }

    public function build(): FileMutationsBuilder
    {
        $fileMutation = new FileMutation(
            $this->originalFileName,
            $this->newFileName,
            $this->lineMutations);
        $this->fileMutationsBuilder->addFileMutation($fileMutation);

        return $this->fileMutationsBuilder;
    }

    public function isAddedFile(): bool
    {
        return (null === $this->originalFileName) && (null !== $this->newFileName);
    }
}
