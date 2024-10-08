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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\FileMutations;

final class FileMutationsBuilder
{
    /**
     * @var FileMutation[]
     */
    private $fileMutations;

    /**
     * FileMutationsBuilder constructor.
     */
    public function __construct()
    {
        $this->fileMutations = [];
    }

    public function addFileMutation(FileMutation $fileMutation): void
    {
        $this->fileMutations[] = $fileMutation;
    }

    public function build(): FileMutations
    {
        return new FileMutations($this->fileMutations);
    }
}
