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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FileAnalysisResultsHolder;
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

    /**
     * Create a string representation of the Analysis results (for persisting to a file).
     *
     * @throws JsonParseException
     * @deprecated https://trello.com/c/Lj8VCsbY
     */
    public function convertToString(AnalysisResults $analysisResults): string
    {
        /** @var FileAnalysisResultsHolder[] $fileAnalysisResultsHolders */
        $fileAnalysisResultsHolders = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $fileName = $analysisResult->getLocation()->getFileName();
            $fileNameAsString = $fileName->getFileName();

            if (!array_key_exists($fileNameAsString, $fileAnalysisResultsHolders)) {
                $fileAnalysisResultsHolders[$fileNameAsString] = new FileAnalysisResultsHolder($fileName);
            }

            $fileAnalysisResultsHolders[$fileNameAsString]->addAnalysisResult($analysisResult);
        }

        $output = '';

        foreach ($fileAnalysisResultsHolders as $fileAnalysisResultsHolder) {
            if ('' !== $output) {
                $output .= str_repeat(PHP_EOL, 3);
            }

            $output .= implode(PHP_EOL, $fileAnalysisResultsHolder->asStrings());
        }

        return $output;
    }
}
