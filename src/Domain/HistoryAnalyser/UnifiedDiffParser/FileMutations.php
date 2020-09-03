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

/**
 * Holds FileMutation objects. Use the.
 */
class FileMutations
{
    /**
     * @var FileMutation[]
     */
    private $fileMutations;

    /**
     * FileMutations constructor.
     *
     * @param FileMutation[] $fileMutations
     */
    public function __construct(array $fileMutations)
    {
        $this->fileMutations = [];
        foreach ($fileMutations as $fileMutation) {
            $this->addFileMutation($fileMutation);
        }
    }

    /**
     * Returns FileMutations for the given file name. Or null if there are no file mutations for that file in the diff.
     */
    public function getFileMutation(NewFileName $newFileName): ?FileMutation
    {
        $newFileNameAsString = $newFileName->getFileName();

        return $this->fileMutations[$newFileNameAsString] ?? null;
    }

    private function addFileMutation(FileMutation $fileMutation): void
    {
        if ($fileMutation->isDeletedFile()) {
            return;
        }
        $newFileNameAsString = $fileMutation->getNewFileName()->getFileName();
        $alreadyExists = array_key_exists($newFileNameAsString, $this->fileMutations);
        Assert::false($alreadyExists, "Multiple new files with name [$newFileNameAsString]");

        $this->fileMutations[$newFileNameAsString] = $fileMutation;
    }

    /**
     * Returns number of FileMutations (only usecase is for testing).
     */
    public function getCount(): int
    {
        return count($this->fileMutations);
    }
}
