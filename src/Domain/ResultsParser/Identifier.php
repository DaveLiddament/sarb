<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

interface Identifier
{
    /**
     * Should be a short code (all identifiers must be unique).
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Human readable description with plenty of detail.
     *
     * E.g. "Psalm results (JSON format)"
     *
     * @return string
     */
    public function getDescription(): string;
}
