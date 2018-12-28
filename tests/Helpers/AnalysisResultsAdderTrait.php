<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

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
        $message = "message-$type";
        $details = "$fileName-$lineNumber-$type-$message";
        $location = new Location(new FileName($fileName), new LineNumber($lineNumber));
        $analysisResult = new AnalysisResult($location, new Type($type), $message, $details);
        $analysisResults->addAnalysisResult($analysisResult);
    }
}
