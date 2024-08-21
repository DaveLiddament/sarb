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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;

final class LineMutation
{
    public static function originalLineNumber(LineNumber $lineNumber): self
    {
        return new self($lineNumber, null);
    }

    public static function newLineNumber(LineNumber $lineNumber): self
    {
        return new self(null, $lineNumber);
    }

    private function __construct(
        private ?LineNumber $originalLine,
        private ?LineNumber $newLine,
    ) {
    }

    public function getNewLine(): ?LineNumber
    {
        return $this->newLine;
    }

    public function getOriginalLine(): ?LineNumber
    {
        return $this->originalLine;
    }

    public function isAdded(): bool
    {
        return null !== $this->newLine;
    }

    /**
     * Returns true if other LineMutation is the same.
     */
    public function isEqual(?self $other): bool
    {
        if (null === $other) {
            return false;
        }

        if (!$this->isLineNumberEqual($this->newLine, $other->newLine)) {
            return false;
        }

        return $this->isLineNumberEqual($this->originalLine, $other->originalLine);
    }

    private function isLineNumberEqual(?LineNumber $a, ?LineNumber $b): bool
    {
        if (null === $a) {
            return null === $b;
        }

        if (null === $b) {
            return false;
        }

        return $a->getLineNumber() === $b->getLineNumber();
    }
}
