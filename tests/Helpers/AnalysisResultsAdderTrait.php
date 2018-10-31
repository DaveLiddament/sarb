<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Type;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\AnalysisResults;

trait AnalysisResultsAdderTrait
{
    /**
     * Adds an AnalysisResult (combination of fileName, lineNumber and type) to AnalysisResults.
     *
     * @param AnalysisResults $analysisResults
     * @param string $fileName
     * @param int $lineNumber
     * @param string $type
     */
    private function addAnalysisResult(
        AnalysisResults $analysisResults,
        string $fileName,
        int $lineNumber,
        string $type
    ): void {
        $details = "$fileName-$lineNumber-$type";
        $location = new Location(new FileName($fileName), new LineNumber($lineNumber));
        $analysisResult = new AnalysisResult($location, new Type($type), $details);
        $analysisResults->addAnalysisResult($analysisResult);
    }
}
