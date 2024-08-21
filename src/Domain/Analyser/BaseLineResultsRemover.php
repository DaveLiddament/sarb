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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;

class BaseLineResultsRemover
{
    /**
     * Returns AnalysisResults stripping out those that appear in the BaseLine.
     */
    public function pruneBaseLine(
        AnalysisResults $latestAnalysisResults,
        HistoryAnalyser $historyAnalyser,
        BaseLineAnalysisResults $baseLineAnalysisResults,
        bool $ignoreWarnings,
    ): AnalysisResults {
        $prunedAnalysisResultsBuilder = new AnalysisResultsBuilder();
        $baseLineResultsComparator = new BaseLineResultsComparator($baseLineAnalysisResults);

        foreach ($latestAnalysisResults->getAnalysisResults() as $analysisResult) {
            if ($analysisResult->getSeverity()->isWarning() && $ignoreWarnings) {
                continue;
            }
            if (!$this->isInHistoricResults($analysisResult, $baseLineResultsComparator, $historyAnalyser)) {
                $prunedAnalysisResultsBuilder->addAnalysisResult($analysisResult);
            }
        }

        return $prunedAnalysisResultsBuilder->build();
    }

    private function isInHistoricResults(
        AnalysisResult $analysisResult,
        BaseLineResultsComparator $baseLineResultsComparator,
        HistoryAnalyser $historyAnalyser,
    ): bool {
        $location = $analysisResult->getLocation();
        $previousLocation = $historyAnalyser->getPreviousLocation($location->getRelativeFileName(), $location->getLineNumber());

        return $baseLineResultsComparator->isInBaseLine($previousLocation, $analysisResult->getType());
    }
}
