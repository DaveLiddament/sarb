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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

/**
 * Used when currently parsing a Change Hunk.
 */
final class ChangeHunkParserState implements State
{
    /**
     * @var int
     */
    private $originalFileLine;

    /**
     * @var int
     */
    private $newFileLine;

    /**
     * ChangeHunkParserState constructor.
     *
     * @throws DiffParseException
     */
    public function __construct(
        private FileMutationBuilder $fileMutationBuilder,
        string $rangeInformationAsString,
    ) {
        $rangeInformation = new RangeInformation($rangeInformationAsString);
        $this->originalFileLine = $rangeInformation->getOriginalFileStartLine();
        $this->newFileLine = $rangeInformation->getNewFileStartLine();
    }

    public function processLine(string $line): State
    {
        if (LineTypeDetector::isStartOfFileDiff($line)) {
            return $this->processNewFileDiffStart();
        }

        if (LineTypeDetector::isStartOfChangeHunk($line)) {
            return $this->processNewChangeHunk($line);
        }

        if (StringUtils::startsWith('+', $line)) {
            $this->processAddLine();

            return $this;
        }

        if (StringUtils::startsWith('-', $line)) {
            $this->processRemoveLine();

            return $this;
        }

        $this->processNoChange();

        return $this;
    }

    private function processNewFileDiffStart(): State
    {
        $fileMutationsBuilder = $this->fileMutationBuilder->build();

        return new FindOriginalFileNameState($fileMutationsBuilder);
    }

    /**
     * @throws DiffParseException
     */
    private function processNewChangeHunk(string $line): State
    {
        return new self($this->fileMutationBuilder, $line);
    }

    private function processAddLine(): void
    {
        $lineMutation = LineMutation::newLineNumber(new LineNumber($this->newFileLine));
        $this->fileMutationBuilder->addLineMutation($lineMutation);
        ++$this->newFileLine;
    }

    private function processRemoveLine(): void
    {
        $lineMutation = LineMutation::originalLineNumber(new LineNumber($this->originalFileLine));
        $this->fileMutationBuilder->addLineMutation($lineMutation);
        ++$this->originalFileLine;
    }

    private function processNoChange(): void
    {
        ++$this->originalFileLine;
        ++$this->newFileLine;
    }

    public function finish(): void
    {
        $this->fileMutationBuilder->build();
    }
}
