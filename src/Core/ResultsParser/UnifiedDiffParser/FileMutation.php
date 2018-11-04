<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser;

use Webmozart\Assert\Assert;

class FileMutation
{
    /**
     * @var OriginalFileName|null
     */
    private $originalFileName;

    /**
     * @var NewFileName|null
     */
    private $newFileName;

    /**
     * @var LineMutation[]
     */
    private $lineMutations;

    /**
     * FileMutation constructor.
     *
     * @param OriginalFileName|null $originalFileName
     * @param NewFileName|null $newFileName
     * @param LineMutation[] $lineMutations
     */
    public function __construct(?OriginalFileName $originalFileName, ?NewFileName $newFileName, array $lineMutations)
    {
        $oneFileSupplied = (null !== $originalFileName) || (null !== $newFileName);
        Assert::true($oneFileSupplied, 'At least 1 originalFileName or newFileName must be supplied');

        $this->originalFileName = $originalFileName;
        $this->newFileName = $newFileName;
        $this->lineMutations = $lineMutations;
    }

    /**
     * @return OriginalFileName
     */
    public function getOriginalFileName(): OriginalFileName
    {
        Assert::notNull($this->originalFileName);

        return $this->originalFileName;
    }

    /**
     * @return NewFileName
     */
    public function getNewFileName(): NewFileName
    {
        Assert::notNull($this->newFileName);

        return $this->newFileName;
    }

    /**
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
