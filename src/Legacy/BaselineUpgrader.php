<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Legacy;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineExporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactoryLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryFactoryException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryMarkerException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

class BaselineUpgrader
{
    public function __construct(
        private FileReader $fileReader,
        private LegacyResultsParserConverter $legacyResultsParserConverter,
        private HistoryFactoryLookupService $historyFactoryLookupService,
        private BaseLineExporter $baseLineExporter,
    ) {
    }

    /**
     * @throws BaseLineImportException
     * @throws FileAccessException
     */
    public function upgrade(BaseLineFileName $baseLineFileName): Identifier
    {
        try {
            $baseLineData = $this->fileReader->readJsonFile($baseLineFileName);

            $historyMarkerAsString = ArrayUtils::getStringValue($baseLineData, BaseLine::HISTORY_MARKER);
            $analysisResultsAsArray = ArrayUtils::getArrayValue($baseLineData, BaseLine::ANALYSIS_RESULTS);
            $resultsParserName = ArrayUtils::getStringValue($baseLineData, BaseLine::RESULTS_PARSER);
            $historyAnalyserName = ArrayUtils::getStringValue($baseLineData, BaseLine::HISTORY_ANALYSER);

            $resultsParser = $this->legacyResultsParserConverter->getNewResultsParser($resultsParserName);
            $historyFactory = $this->historyFactoryLookupService->getHistoryFactory($historyAnalyserName);

            $historyMarker = $historyFactory->newHistoryMarkerFactory()->newHistoryMarker($historyMarkerAsString);
            $analysisResults = BaseLineAnalysisResults::fromArray($analysisResultsAsArray);

            $baseline = new BaseLine($historyFactory, $analysisResults, $resultsParser, $historyMarker);
        } catch (
            ArrayParseException|
            InvalidResultsParserException|
            InvalidHistoryFactoryException|
            InvalidHistoryMarkerException|
            InvalidContentTypeException|
            ParseAtLocationException $e) {
                throw BaseLineImportException::fromException($baseLineFileName, $e);
            }
        $this->baseLineExporter->export($baseline, $baseLineFileName);

        return $resultsParser->getIdentifier();
    }
}
