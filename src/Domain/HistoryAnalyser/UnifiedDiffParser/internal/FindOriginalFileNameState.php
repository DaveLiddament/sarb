<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

final class FindOriginalFileNameState implements State
{
    private const RENAME_FROM = 'rename from ';
    private const ORIGINAL_FILE = '--- a/';

    /**
     * @var FileMutationBuilder
     */
    private $fileMutationBuilder;

    /**
     * FindOriginalFileNameState constructor.
     */
    public function __construct(FileMutationsBuilder $fileMutationsBuilder)
    {
        $this->fileMutationBuilder = new FileMutationBuilder($fileMutationsBuilder);
    }

    public function processLine(string $line): State
    {
        if (LineTypeDetector::isFileAdded($line)) {
            return new FindNewFileNameState($this->fileMutationBuilder);
        }

        if (StringUtils::startsWith(self::RENAME_FROM, $line)) {
            return $this->processAsRename($line);
        }

        if (StringUtils::startsWith(self::ORIGINAL_FILE, $line)) {
            return $this->processAsChange($line);
        }

        // A change hunk can only appear after the file names. Reaching one whilst still looking
        // for the original file name means the diff is in a format that was not recognised
        // (e.g. quoted paths or non standard prefixes). Fail rather than silently skip the file.
        if (LineTypeDetector::isStartOfChangeHunk($line)) {
            throw DiffParseException::unrecognisedFileDiffFormat($line);
        }

        return $this;
    }

    private function processAsRename(string $line): State
    {
        $originalFileName = StringUtils::removeFromStart(self::RENAME_FROM, $line);
        $this->fileMutationBuilder->setOriginalFileName(new OriginalFileName($originalFileName));

        return new FindRenameToState($this->fileMutationBuilder);
    }

    private function processAsChange(string $line): State
    {
        $originalFileName = StringUtils::removeFromStart(self::ORIGINAL_FILE, $line);
        $this->fileMutationBuilder->setOriginalFileName(new OriginalFileName($originalFileName));

        return new FindNewFileNameState($this->fileMutationBuilder);
    }

    /**
     * Signifies that the diff has finished.
     */
    public function finish(): void
    {
        // Nothing to do.
    }
}
