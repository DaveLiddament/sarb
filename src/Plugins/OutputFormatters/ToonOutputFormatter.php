<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ToonUtils;

final class ToonOutputFormatter implements OutputFormatter
{
    private const KEY = 'issues';

    /** @var list<string> */
    private const FIELDS = ['file', 'line', 'type', 'message', 'severity'];

    public function outputResults(
        AnalysisResults $analysisResults,
    ): string {
        $rows = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();
            $rows[] = [
                'file' => $location->getRelativeFileName()->getFileName(),
                'line' => $location->getLineNumber()->getLineNumber(),
                'type' => $analysisResult->getType()->getType(),
                'message' => $analysisResult->getMessage(),
                'severity' => $analysisResult->getSeverity()->getSeverity(),
            ];
        }

        return ToonUtils::encodeTable(self::KEY, self::FIELDS, $rows);
    }

    public function getIdentifier(): string
    {
        return 'toon';
    }
}
