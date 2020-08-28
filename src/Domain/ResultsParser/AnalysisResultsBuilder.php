<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

class AnalysisResultsBuilder
{
    /**
     * @var AnalysisResult[]
     */
    private $analysisResults  = [];

    public function addAnalysisResult(AnalysisResult $analysisResult): void
    {
        $this->analysisResults[] = $analysisResult;
    }

    public function build(): AnalysisResults
    {
        return new AnalysisResults($this->analysisResults);
    }
}
