<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\internal\BaseLineResultsComparator;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;

class BaseLineResultsRemover
{
    /**
     * Returns AnalysisResults stripping out those that appear in the BaseLine.
     *
     * @param AnalysisResults $latestAnalysisResults
     * @param BaseLine $baseLine
     *
     * @throws HistoryAnalyserException
     *
     * @return AnalysisResults
     */
    public function pruneBaseLine(
        AnalysisResults $latestAnalysisResults,
        BaseLine $baseLine
    ): AnalysisResults {
        $historyAnalyser = $baseLine->getHistoryFactory()->newHistoryAnalyser($baseLine->getHistoryMarker());

        $prunedAnalysisResults = new AnalysisResults();
        $baseLineResultsComparator = new BaseLineResultsComparator($baseLine->getAnalysisResults());

        foreach ($latestAnalysisResults->getAnalysisResults() as $analysisResult) {
            if (!$this->isInHistoricResults($analysisResult, $baseLineResultsComparator, $historyAnalyser)) {
                $prunedAnalysisResults->addAnalysisResult($analysisResult);
            }
        }

        return $prunedAnalysisResults;
    }

    private function isInHistoricResults(
        AnalysisResult $analysisResult,
        BaseLineResultsComparator $baseLineResultsComparator,
        HistoryAnalyser $historyAnalyser
    ): bool {
        $previousLocation = $historyAnalyser->getPreviousLocation($analysisResult->getLocation());

        // Analysis result refers to a Location not in the BaseLine, then this is not an historic analysis result.
        if ($previousLocation->isNoPreviousLocation()) {
            return false;
        }

        // Now check through to history AnalysisResults to see if there is an exact match.
        return $baseLineResultsComparator->isInBaseLine($previousLocation->getLocation(), $analysisResult->getType());
    }
}
