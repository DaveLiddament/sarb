<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

final class PrunedResults
{
    public function __construct(
        private BaseLine $baseLine,
        private AnalysisResults $prunedResults,
        private AnalysisResults $inputAnalysisResults,
    ) {
    }

    public function getBaseLine(): BaseLine
    {
        return $this->baseLine;
    }

    public function getPrunedResults(): AnalysisResults
    {
        return $this->prunedResults;
    }

    public function getInputAnalysisResults(): AnalysisResults
    {
        return $this->inputAnalysisResults;
    }

    /**
     * Returns the input results that matched the baseline (i.e. input results minus the pruned ones).
     */
    public function getBaseLinedResults(): AnalysisResults
    {
        $prunedAnalysisResults = $this->prunedResults->getAnalysisResults();

        $baseLinedResults = [];
        foreach ($this->inputAnalysisResults->getAnalysisResults() as $analysisResult) {
            if (!in_array($analysisResult, $prunedAnalysisResults, true)) {
                $baseLinedResults[] = $analysisResult;
            }
        }

        return new AnalysisResults($baseLinedResults);
    }
}
