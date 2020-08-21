<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\BaseLineResultsRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;

class ResultsPruner implements ResultsPrunerInterface
{
    /**
     * @var BaseLineImporter
     */
    private $baseLineImporter;

    /**
     * @var BaseLineResultsRemover
     */
    private $baseLineResultsRemover;

    public function __construct(BaseLineImporter $baseLineImporter, BaseLineResultsRemover $baseLineResultsRemover)
    {
        $this->baseLineImporter = $baseLineImporter;
        $this->baseLineResultsRemover = $baseLineResultsRemover;
    }

    public function getPrunedResults(
        FileName $baseLineFileName,
        string $inputAnalysisResultsAsString,
        ProjectRoot $projectRoot
    ): PrunedResults {
        $baseLine = $this->baseLineImporter->import($baseLineFileName);
        $resultsParser = $baseLine->getResultsParser();
        $historyFactory = $baseLine->getHistoryFactory();

        $historyAnalyser = $historyFactory->newHistoryAnalyser($baseLine->getHistoryMarker(), $projectRoot);
        $inputAnalysisResults = $resultsParser->convertFromString($inputAnalysisResultsAsString, $projectRoot);

        $outputAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
            $inputAnalysisResults,
            $historyAnalyser,
            $baseLine->getAnalysisResults(),
            $projectRoot
        );

        return new PrunedResults($baseLine, $outputAnalysisResults, $inputAnalysisResults->getCount());
    }
}
