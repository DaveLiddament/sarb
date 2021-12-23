<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class PrunedResults
{
    /**
     * @var BaseLine
     */
    private $baseLine;
    /**
     * @var AnalysisResults
     */
    private $prunedResults;
    /**
     * @var AnalysisResults
     */
    private $inputAnalysisResults;

    public function __construct(
        BaseLine $baseLine,
        AnalysisResults $prunedResults,
        AnalysisResults $inputAnalysisResults
    ) {
        $this->baseLine = $baseLine;
        $this->prunedResults = $prunedResults;
        $this->inputAnalysisResults = $inputAnalysisResults;
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
