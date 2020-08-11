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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

interface ResultsParser
{
    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     *
     * @throws ParseAtLocationException
     * @throws InvalidFileFormatException
     */
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults;

    /**
     * Returns the identifier of the Results Parser.
     */
    public function getIdentifier(): Identifier;

    /**
     * Returns true if the ResultsParser has to guess the violation type.
     *
     * See docs/ViolationTypeClassificationGuessing.md
     */
    public function showTypeGuessingWarning(): bool;
}
