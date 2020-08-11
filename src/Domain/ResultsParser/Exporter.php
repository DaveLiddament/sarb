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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;

/**
 * Writes AnalysisResults to disk (via the FileWriter).
 * @deprecated https://trello.com/c/Lj8VCsbY
 */
class Exporter
{
    /**
     * @var FileWriter
     */
    private $fileWriter;

    /**
     * Exporter constructor.
     */
    public function __construct(FileWriter $fileWriter)
    {
        $this->fileWriter = $fileWriter;
    }

    /**
     * Exports AnalysisResults to the given $outputFile.
     *
     * @throws JsonParseException
     * @throws FileAccessException
     */
    public function exportAnalysisResults(
        AnalysisResults $analysisResults,
        ResultsParser $resultsParser,
        FileName $outputFile
    ): void {
        $fileContents = $resultsParser->convertToString($analysisResults);
        $this->fileWriter->writeFile($outputFile, $fileContents);
    }
}
