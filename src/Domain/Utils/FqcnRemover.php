<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

class FqcnRemover
{
    /**
     * Removes anything that looks like a FQCN from the string.
     *
     * @param string $raw
     *
     * @return string
     */
    public function removeRqcn(string $raw): string
    {
        $parts = explode(' ', $raw);
        $outputParts = [];
        foreach ($parts as $part) {
            if (false === strpos($part, '\\')) {
                $outputParts[] = $part;
            }
        }

        return implode(' ', $outputParts);
    }
}
