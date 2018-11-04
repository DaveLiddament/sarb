<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileWriter;

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
     */
    public function export(BaseLine $baseLine, FileName $fileName): void
    {
        $asArray = [
            BaseLine::HISTORY_MARKER => $baseLine->getHistoryMarker()->asString(),
            BaseLine::ANALYSER => $baseLine->getIdentifier()->getCode(),
            BaseLine::ANALYSIS_RESULTS => $baseLine->getAnalysisResults()->asArray(),
        ];

        $this->fileWriter->writeArrayToFile($fileName, $asArray);
    }
}
