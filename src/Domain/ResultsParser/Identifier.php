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
     */
    public function getCode(): string;

    /**
     * Human readable name.
     *
     * E.g. "Psalm results (JSON format)."
     */
    public function getDescription(): string;

    /**
     * Command to run to make the tool output in correct format.
     *
     * E.g  "psalm --output-format=json"
     */
    public function getToolCommand(): string;
}
