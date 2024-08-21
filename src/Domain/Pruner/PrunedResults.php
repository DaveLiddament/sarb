<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class PrunedResults
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
}
