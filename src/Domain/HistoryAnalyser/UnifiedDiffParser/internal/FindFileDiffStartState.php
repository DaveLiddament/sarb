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

final class FindFileDiffStartState implements State
{
    /**
     * FindFileDiffStartState constructor.
     */
    public function __construct(
        private FileMutationsBuilder $fileMutationsBuilder,
    ) {
    }

    public function processLine(string $line): State
    {
        if (LineTypeDetector::isStartOfFileDiff($line)) {
            return new FindOriginalFileNameState($this->fileMutationsBuilder);
        }

        return $this;
    }

    public function finish(): void
    {
        // Nothing to do. Not in the middle of anything.
    }
}
