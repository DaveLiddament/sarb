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

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\JsonParseException;

/**
 * Writes AnalysisResults to disk (via the FileWriter).
 */
class Exporter
{
    /**
     * @var FileWriter
     */
    private $fileWriter;

    /**
     * Exporter constructor.
     *
     * @param FileWriter $fileWriter
     */
    public function __construct(FileWriter $fileWriter)
    {
        $this->fileWriter = $fileWriter;
    }

    /**
     * Exports AnalysisResults to the given $outputFile.
     *
     * @param AnalysisResults $analysisResults
     * @param StaticAnalysisResultsParser $resultsParser
     * @param FileName $outputFile
     *
     * @throws JsonParseException
     * @throws FileAccessException
     */
    public function exportAnalysisResults(
        AnalysisResults $analysisResults,
        StaticAnalysisResultsParser $resultsParser,
        FileName $outputFile
    ): void {
        $fileContents = $resultsParser->convertToString($analysisResults);
        $this->fileWriter->writeFile($outputFile, $fileContents);
    }
}
