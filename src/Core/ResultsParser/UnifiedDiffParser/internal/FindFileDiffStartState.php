<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\internal;

class FindFileDiffStartState implements State
{
    /**
     * @var FileMutationsBuilder
     */
    private $fileMutationsBuilder;

    /**
     * FindFileDiffStartState constructor.
     *
     * @param FileMutationsBuilder $fileMutationsBuilder
     */
    public function __construct(FileMutationsBuilder $fileMutationsBuilder)
    {
        $this->fileMutationsBuilder = $fileMutationsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function processLine(string $line): State
    {
        if (LineTypeDetector::isStartOfFileDiff($line)) {
            return new FindOriginalFileNameState($this->fileMutationsBuilder);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        // Nothing to do. Not in the middle of anything.
    }
}
