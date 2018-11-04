<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\FileMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\OriginalFileName;
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
     * @var LineMutation[]
     */
    private $lineMutations;

    /**
     * FileMutationBuilder constructor.
     *
     * @param FileMutationsBuilder $fileMutationsBuilder
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
