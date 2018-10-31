<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\File\FileWriter;

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
     * @param \DaveLiddament\StaticAnalysisBaseliner\Core\File\FileWriter $fileWriter
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
