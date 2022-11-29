<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;

trait AnalysisResultsAdderTrait
{
    /**
     * Adds an AnalysisResult (combination of fileName, lineNumber and type) to AnalysisResults.
     */
    private function addAnalysisResult(
        AnalysisResultsBuilder $analysisResultsBuilder,
        ProjectRoot $projectRoot,
        string $absoluteFileName,
        int $lineNumber,
        string $type
    ): void {
        $analysisResult = $this->buildAnalysisResult($projectRoot, $absoluteFileName, $lineNumber, $type);
        $analysisResultsBuilder->addAnalysisResult($analysisResult);
    }

    private function buildAnalysisResult(
        ProjectRoot $projectRoot,
        string $absoluteFileName,
        int $lineNumber,
        string $type
    ): AnalysisResult {
        $message = "message-$type";

        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName($absoluteFileName),
            $projectRoot,
            new LineNumber($lineNumber)
        );

        return new AnalysisResult($location, new Type($type), $message, [], Severity::error());
    }
}
