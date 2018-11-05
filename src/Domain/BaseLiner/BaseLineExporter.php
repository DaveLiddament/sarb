<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;

/**
 * Exports a BaseLine to a file.
 */
class BaseLineExporter
{
    /**
     * @var FileWriter
     */
    private $fileWriter;

    /**
     * BaseLineExporter constructor.
     *
     * @param FileWriter $fileWriter
     */
    public function __construct(FileWriter $fileWriter)
    {
        $this->fileWriter = $fileWriter;
    }

    /**
     * Export BaseLine results to the given FileName.
     *
     * @param BaseLine $baseLine
     * @param FileName $fileName
     *
     * @throws FileAccessException
     * @throws JsonParseException
     */
    public function export(BaseLine $baseLine, FileName $fileName): void
    {
        $asArray = [
            BaseLine::HISTORY_ANALYSER => $baseLine->getHistoryFactory()->getIdentifier(),
            BaseLine::HISTORY_MARKER => $baseLine->getHistoryMarker()->asString(),
            BaseLine::RESULTS_PARSER => $baseLine->getResultsParser()->getIdentifier()->getCode(),
            BaseLine::ANALYSIS_RESULTS => $baseLine->getAnalysisResults()->asArray(),
        ];

        $this->fileWriter->writeArrayToFile($fileName, $asArray);
    }
}
