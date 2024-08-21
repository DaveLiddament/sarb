<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser;

use Webmozart\Assert\Assert;

class FileMutation
{
    /**
     * @param list<LineMutation> $lineMutations
     */
    public function __construct(
        private ?OriginalFileName $originalFileName,
        private ?NewFileName $newFileName,
        private array $lineMutations,
    ) {
        $oneFileSupplied = (null !== $originalFileName) || (null !== $newFileName);
        Assert::true($oneFileSupplied, 'At least 1 originalFileName or newFileName must be supplied');
    }

    public function getOriginalFileName(): OriginalFileName
    {
        Assert::notNull($this->originalFileName);

        return $this->originalFileName;
    }

    public function getNewFileName(): NewFileName
    {
        Assert::notNull($this->newFileName);

        return $this->newFileName;
    }

    /**
     * @psalm-return list<LineMutation>
     *
     * @return LineMutation[]
     */
    public function getLineMutations(): array
    {
        return $this->lineMutations;
    }

    public function isAddedFile(): bool
    {
        return (null === $this->originalFileName) && (null !== $this->newFileName);
    }

    public function isDeletedFile(): bool
    {
        return (null !== $this->originalFileName) && (null === $this->newFileName);
    }
}
