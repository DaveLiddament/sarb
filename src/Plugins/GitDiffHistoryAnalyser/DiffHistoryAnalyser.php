<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\FileMutations;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\OriginalLineNumberCalculator;

class DiffHistoryAnalyser implements HistoryAnalyser
{
    /**
     * @var FileMutations
     */
    private $fileMutations;

    /**
     * DiffHistoryAnalyser constructor.
     */
    public function __construct(FileMutations $fileMutations)
    {
        $this->fileMutations = $fileMutations;
    }

    /**
     * Returns the location of the line number in the baseline (if it exists).
     */
    public function getPreviousLocation(RelativeFileName $fileName, LineNumber $lineNumber): PreviousLocation
    {
        $newFileName = new NewFileName($fileName->getFileName());

        $fileMutation = $this->fileMutations->getFileMutation($newFileName);

        // If not in file mutations then no change to code
        if (null === $fileMutation) {
            return PreviousLocation::fromFileNameAndLineNumber($fileName, $lineNumber);
        }

        // If file added then this is not in the baseline.
        if ($fileMutation->isAddedFile()) {
            return PreviousLocation::noPreviousLocation();
        }

        $originalLineNumber = OriginalLineNumberCalculator::calculateOriginalLineNumber(
            $fileMutation,
            $lineNumber->getLineNumber()
        );

        if (null === $originalLineNumber) {
            return PreviousLocation::noPreviousLocation();
        }

        return PreviousLocation::fromFileNameAndLineNumber(
            $fileMutation->getOriginalFileName(),
            new LineNumber($originalLineNumber)
        );
    }
}
