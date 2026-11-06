<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\BaseLineResultsRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ErrorReportedByStaticAnalysisTool;

final class ResultsPruner implements ResultsPrunerInterface
{
    public function __construct(
        private BaseLineImporter $baseLineImporter,
        private BaseLineResultsRemover $baseLineResultsRemover,
        private AnalysisResultsImporter $analysisResultsImporter,
    ) {
    }

    /**
     * @throws BaseLineImportException
     * @throws FileAccessException
     * @throws AnalysisResultsImportException
     * @throws HistoryAnalyserException
     * @throws ErrorReportedByStaticAnalysisTool
     * @throws InputMissingTypeIdentifiersException
     */
    public function getPrunedResults(
        BaseLineFileName $baseLineFileName,
        string $analysisResults,
        ProjectRoot $projectRoot,
        bool $ignoreWarnings,
    ): PrunedResults {
        $baseLine = $this->baseLineImporter->import($baseLineFileName);
        $resultsParser = $baseLine->getResultsParser();
        $historyFactory = $baseLine->getHistoryFactory();

        $historyAnalyser = $historyFactory->newHistoryAnalyser($baseLine->getHistoryMarker(), $projectRoot);
        $inputAnalysisResults = $this->analysisResultsImporter->import($resultsParser, $projectRoot, $analysisResults);

        if ($baseLine->getTypeIdentifiersUsage()->isFromToolIdentifiers()
            && !$inputAnalysisResults->hasNoIssues()
            && !$inputAnalysisResults->getTypeIdentifiersUsage()->isFromToolIdentifiers()
        ) {
            throw InputMissingTypeIdentifiersException::baseLineBuiltFromTypeIdentifiers();
        }

        $outputAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
            $inputAnalysisResults,
            $historyAnalyser,
            $baseLine->getAnalysisResults(),
            $ignoreWarnings,
        );

        return new PrunedResults($baseLine, $outputAnalysisResults, $inputAnalysisResults);
    }
}
