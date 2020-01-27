<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal;

interface State
{
    /**
     * @throws DiffParseException
     *
     * @return State
     */
    public function processLine(string $line): self;

    /**
     * Signifies that the diff has finished.
     *
     * @throws DiffParseException
     */
    public function finish(): void;
}
