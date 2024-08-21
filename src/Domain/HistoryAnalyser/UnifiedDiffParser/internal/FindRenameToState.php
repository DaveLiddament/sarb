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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

/**
 * Previous line in diff was renaming a file from Original Name. This is looking for the New Name.
 */
final class FindRenameToState implements State
{
    public const RENAME_TO = 'rename to ';

    /**
     * FindRenameToState constructor.
     */
    public function __construct(
        private FileMutationBuilder $fileMutationBuilder,
    ) {
    }

    public function processLine(string $line): State
    {
        if (!StringUtils::startsWith(self::RENAME_TO, $line)) {
            throw DiffParseException::missingRenameTo($line);
        }

        $newFileName = StringUtils::removeFromStart(self::RENAME_TO, $line);
        $this->fileMutationBuilder->setNewFileName(new NewFileName($newFileName));

        return new FindChangeHunkStartState($this->fileMutationBuilder);
    }

    public function finish(): void
    {
        throw DiffParseException::missingRenameTo('<EOF>');
    }
}
