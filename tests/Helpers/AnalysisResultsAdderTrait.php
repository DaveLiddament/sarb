<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;

trait AnalysisResultsAdderTrait
{
    /**
     * Adds an AnalysisResult (combination of fileName, lineNumber and type) to AnalysisResults.
     */
    private function addAnalysisResult(
        AnalysisResultsBuilder $analysisResultsBuilder,
        string $fileName,
        int $lineNumber,
        string $type
    ): void {
        $analysisResult = $this->buildAnalysisResult($fileName, $lineNumber, $type);
        $analysisResultsBuilder->addAnalysisResult($analysisResult);
    }

    private function buildAnalysisResult(
        string $fileName,
        int $lineNumber,
        string $type
    ): AnalysisResult {
        $message = "message-$type";
        $details = "$fileName-$lineNumber-$type-$message";
        $location = new Location(new FileName($fileName), new LineNumber($lineNumber));

        return new AnalysisResult($location, new Type($type), $message, $details);
    }
}
