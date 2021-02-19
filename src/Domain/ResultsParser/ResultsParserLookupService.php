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

interface ResultsParserLookupService
{
    /**
     * Returns ResultsParser of the given name.
     *
     * @throws InvalidResultsParserException
     */
    public function getResultsParser(string $name): ResultsParser;

    /**
     * @return ResultsParser[]
     */
    public function getAll(): array;

    /**
     * Returns a list of all ResultsParser codes.
     *
     * These are used to identify which Static Analysis tool is being used for generating a baseline or comparing
     * baseline results to.
     *
     * @return string[]
     */
    public function getIdentifiers(): array;
}
