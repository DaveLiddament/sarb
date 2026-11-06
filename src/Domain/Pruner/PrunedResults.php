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
     * Returns true if the input results contain types from tool provided identifiers, but the
     * baseline was created before the tool provided them (so matching fell back to legacy types).
     * Regenerating the baseline would make it use the identifiers.
     */
    public function shouldRecommendRegeneratingBaseLine(): bool
    {
        return !$this->baseLine->getTypeIdentifiersUsage()->isFromToolIdentifiers()
            && $this->baseLine->getAnalysisResults()->getCount() > 0
            && $this->inputAnalysisResults->getTypeIdentifiersUsage()->isFromToolIdentifiers();
    }
}
