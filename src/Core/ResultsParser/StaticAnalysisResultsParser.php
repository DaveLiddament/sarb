<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ParseAtLocationException;

interface StaticAnalysisResultsParser
{
    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     *
     * @param string $resultsAsString
     *
     * @throws ParseAtLocationException
     * @throws InvalidFileFormatException
     *
     * @return AnalysisResults
     */
    public function convertFromString(string $resultsAsString): AnalysisResults;

    /**
     * Create a string representation of the Analysis results (for persisting to a file).
     *
     * @param AnalysisResults $analysisResults
     *
     * @throws JsonParseException
     *
     * @return string
     */
    public function convertToString(AnalysisResults $analysisResults): string;

    /**
     * Returns the identifier of the Results Parser.
     *
     * @return Identifier
     */
    public function getIdentifier(): Identifier;

    /**
     * TODO: can this be removed?
     *
     * Converts from an array.
     *
     * @param array $analysisResults
     *
     * @throws ParseAtLocationException
     *
     * @return AnalysisResults
     */
    public function convertFromArray(array $analysisResults): AnalysisResults;
}
