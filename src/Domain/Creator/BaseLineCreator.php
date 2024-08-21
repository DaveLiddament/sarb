<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Creator;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineExporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

class BaseLineCreator implements BaseLineCreatorInterface
{
    public function __construct(
        private BaseLineExporter $exporter,
        private AnalysisResultsImporter $analysisResultsImporter,
    ) {
    }

    public function createBaseLine(
        HistoryFactory $historyFactory,
        ResultsParser $resultsParser,
        BaseLineFileName $baselineFile,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString,
        bool $forceBaselineCreation,
    ): BaseLine {
        $historyMarker = $historyFactory->newHistoryMarkerFactory()->newCurrentHistoryMarker($projectRoot, $forceBaselineCreation);
        $analysisResults = $this->analysisResultsImporter->import($resultsParser, $projectRoot, $analysisResultsAsString);
        $baseLineAnalysisResults = BaseLineAnalysisResults::fromAnalysisResults($analysisResults);
        $baseline = new BaseLine($historyFactory, $baseLineAnalysisResults, $resultsParser, $historyMarker);
        $this->exporter->export($baseline, $baselineFile);

        return $baseline;
    }
}
