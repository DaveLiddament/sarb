<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

final class GithubActionsOutputFormatter implements OutputFormatter
{
    public function outputResults(
        AnalysisResults $analysisResults,
    ): string {
        $lines = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();

            $message = str_replace("\n", '%0A', $analysisResult->getMessage());
            $lines[] = sprintf(
                '::%s file=%s,line=%d,title=%s::%s',
                $analysisResult->getSeverity()->getSeverity(),
                $location->getRelativeFileName()->getFileName(),
                $location->getLineNumber()->getLineNumber(),
                $this->escapeProperty($analysisResult->getType()->getType()),
                $message,
            );
        }

        return implode(\PHP_EOL, $lines);
    }

    public function getIdentifier(): string
    {
        return 'github';
    }

    /**
     * Escapes a workflow command property value. Unlike the message, property values must also
     * have ':' and ',' escaped, otherwise they would terminate the property list.
     */
    private function escapeProperty(string $value): string
    {
        return str_replace(
            ['%', "\r", "\n", ':', ','],
            ['%25', '%0D', '%0A', '%3A', '%2C'],
            $value,
        );
    }
}
