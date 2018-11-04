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

/**
 * Looks for:
 * - start of a new Change Hunk
 * - start of a new File Diff.
 */
class FindChangeHunkStartState implements State
{
    /**
     * @var FileMutationBuilder
     */
    private $fileMutationBuilder;

    /**
     * FindChangeHunkStartState constructor.
     *
     * @param FileMutationBuilder $fileMutationBuilder
     */
    public function __construct(FileMutationBuilder $fileMutationBuilder)
    {
        $this->fileMutationBuilder = $fileMutationBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function processLine(string $line): State
    {
        if (LineTypeDetector::isStartOfFileDiff($line)) {
            return $this->processDiffStart();
        }

        if (LineTypeDetector::isStartOfChangeHunk($line)) {
            return $this->processChangeHunkStart($line);
        }

        return $this;
    }

    private function processDiffStart(): State
    {
        $fileMutationsBuilder = $this->fileMutationBuilder->build();

        return new FindOriginalFileNameState($fileMutationsBuilder);
    }

    private function processChangeHunkStart(string $line): State
    {
        return new ChangeHunkParserState($this->fileMutationBuilder, $line);
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        $this->fileMutationBuilder->build();
    }
}
