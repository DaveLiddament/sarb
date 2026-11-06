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

            $lines[] = sprintf(
                '::%s file=%s,line=%d::%s',
                $analysisResult->getSeverity()->getSeverity(),
                $this->escapeProperty($location->getRelativeFileName()->getFileName()),
                $location->getLineNumber()->getLineNumber(),
                $this->escapeData($analysisResult->getMessage()),
            );
        }

        return implode(\PHP_EOL, $lines);
    }

    public function getIdentifier(): string
    {
        return 'github';
    }

    /**
     * Escapes a workflow command message. The GitHub Actions runner URL decodes these sequences,
     * so a literal '%' must be escaped too, otherwise it corrupts the annotation.
     */
    private function escapeData(string $value): string
    {
        return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $value);
    }

    /**
     * Escapes a workflow command property value. Unlike the message, property values must also
     * have ':' and ',' escaped, otherwise they would terminate the property list.
     */
    private function escapeProperty(string $value): string
    {
        return str_replace(['%', "\r", "\n", ':', ','], ['%25', '%0D', '%0A', '%3A', '%2C'], $value);
    }
}
