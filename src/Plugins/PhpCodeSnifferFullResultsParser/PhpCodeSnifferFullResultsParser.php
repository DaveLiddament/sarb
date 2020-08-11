<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FileNameLineParserState;

class PhpCodeSnifferFullResultsParser implements ResultsParser
{
    public const FULL_FILE_NAME = 'fullFileName';
    public const LEVEL = 'level';
    public const FIXABLE = 'fixable';

    public function getIdentifier(): Identifier
    {
        return new PhpCodeSnifferFullIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }

    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     *
     * @throws ParseAtLocationException
     * @throws InvalidFileFormatException
     */
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResults = new AnalysisResults();
        $lineParser = new FileNameLineParserState($analysisResults, $projectRoot);
        $lines = explode(PHP_EOL, $resultsAsString);
        foreach ($lines as $line) {
            $lineParser = $lineParser->parseLine($line);
        }

        return $analysisResults;
    }
}
