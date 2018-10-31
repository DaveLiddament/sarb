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

use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\Utils\StringUtils;

/**
 * Previous line in diff was renaming a file from Original Name. This is looking for the New Name.
 */
class FindRenameToState implements State
{
    const RENAME_TO = 'rename to ';

    /**
     * @var FileMutationBuilder
     */
    private $fileMutationBuilder;

    /**
     * FindRenameToState constructor.
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
        if (!StringUtils::startsWith(self::RENAME_TO, $line)) {
            throw DiffParseException::missingRenameTo($line);
        }

        $newFileName = StringUtils::removeFromStart(self::RENAME_TO, $line);
        $this->fileMutationBuilder->setNewFileName(new NewFileName($newFileName));

        return new FindChangeHunkStartState($this->fileMutationBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function finish(): void
    {
        throw DiffParseException::missingRenameTo('<EOF>');
    }
}
