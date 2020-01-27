<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

/**
 * Holds current Original and New Line numbers.
 */
class LineNumberMapper
{
    /**
     * @var int
     */
    private $originalLineNumber;

    /**
     * @var int
     */
    private $newLineNumber;

    public function __construct()
    {
        $this->originalLineNumber = 0;
        $this->newLineNumber = 0;
    }

    public function incrementBoth(): void
    {
        ++$this->originalLineNumber;
        ++$this->newLineNumber;
    }

    public function incrementOriginal(): void
    {
        ++$this->originalLineNumber;
    }

    public function incrementNew(): void
    {
        ++$this->newLineNumber;
    }

    public function getOriginalLineNumber(): int
    {
        return $this->originalLineNumber;
    }

    public function getNewLineNumber(): int
    {
        return $this->newLineNumber;
    }
}
