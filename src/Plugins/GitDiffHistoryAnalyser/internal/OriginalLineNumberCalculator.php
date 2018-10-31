<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\FileMutation;
use Webmozart\Assert\Assert;

class OriginalLineNumberCalculator
{
    /**
     * Returns original line number or null if the line was added in the mutation given the file mutations.
     *
     * @param FileMutation $fileMutation
     * @param int $newLineNumber
     *
     * @return int|null
     */
    public static function calculateOriginalLineNumber(FileMutation $fileMutation, int $newLineNumber): ?int
    {
        $lineNumberMapper = new LineNumberMapper();

        foreach ($fileMutation->getLineMutations() as $lineMutation) {
            if ($lineMutation->isAdded()) {
                $newLine = $lineMutation->getNewLine();
                Assert::notNull($newLine);
                $targetNewLineNumber = $newLine->getLineNumber();

                if ($newLineNumber === $targetNewLineNumber) {
                    return null;
                }

                while ($lineNumberMapper->getNewLineNumber() < $targetNewLineNumber) {
                    if ($lineNumberMapper->getNewLineNumber() === $newLineNumber) {
                        return $lineNumberMapper->getOriginalLineNumber();
                    }

                    $lineNumberMapper->incrementBoth();
                }

                $lineNumberMapper->incrementNew();
            } else {
                $originalLine = $lineMutation->getOriginalLine();
                Assert::notNull($originalLine);
                $targetOriginalLineNumber = $originalLine->getLineNumber();

                while ($lineNumberMapper->getOriginalLineNumber() < $targetOriginalLineNumber) {
                    if ($lineNumberMapper->getNewLineNumber() === $newLineNumber) {
                        return $lineNumberMapper->getOriginalLineNumber();
                    }

                    $lineNumberMapper->incrementBoth();
                }

                $lineNumberMapper->incrementOriginal();
            }
        }

        $differenceNewLineAndCurrentNewLine = $newLineNumber - $lineNumberMapper->getNewLineNumber();

        return $lineNumberMapper->getOriginalLineNumber() + $differenceNewLineAndCurrentNewLine;
    }
}
