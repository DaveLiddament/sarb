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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

interface ResultsParser
{
    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     *
     * @param string $resultsAsString
     * @param ProjectRoot $projectRoot
     *
     * @throws ParseAtLocationException
     * @throws InvalidFileFormatException
     *
     * @return AnalysisResults
     */
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults;

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
     * Returns true if the ResultsParser has to guess the violation type.
     *
     * See docs/ViolationTypeClassificationGuessing.md
     *
     * @return bool
     */
    public function showTypeGuessingWarning(): bool;
}
