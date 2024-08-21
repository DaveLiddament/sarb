<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

/**
 * Use case for this is when static analysis tools don't provide a classification for the type of bug found.
 * See docs/ViolationTypeClassificationGuessing.md.
 */
final class FqcnRemover
{
    /**
     * Removes anything that looks like a FQCN from the string.
     */
    public function removeRqcn(string $raw): string
    {
        $parts = explode(' ', $raw);
        $outputParts = [];
        foreach ($parts as $part) {
            if (!str_contains($part, '\\')) {
                $outputParts[] = $part;
            }
        }

        return implode(' ', $outputParts);
    }
}
