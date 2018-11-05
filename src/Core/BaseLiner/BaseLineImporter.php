<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactoryLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\InvalidHistoryFactoryException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\ResultsParserLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\JsonParseException;

/**
 * Imports a baseline from the file.
 */
class BaseLineImporter
{
    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var HistoryFactoryLookupService
     */
    private $historyFactoryLookupService;

    /**
     * @var ResultsParserLookupService
     */
    private $resultsParserLookupService;

    /**
     * BaseLineImporter constructor.
     *
     * @param FileReader $fileReader
     * @param ResultsParserLookupService $resultsParserLookupService
     * @param HistoryFactoryLookupService $historyFactoryLookupService
     */
    public function __construct(
        FileReader $fileReader,
        ResultsParserLookupService $resultsParserLookupService,
        HistoryFactoryLookupService $historyFactoryLookupService
    ) {
        $this->fileReader = $fileReader;
        $this->resultsParserLookupService = $resultsParserLookupService;
        $this->historyFactoryLookupService = $historyFactoryLookupService;
    }

    /**
     * Imports baseline results.
     *
     * @param FileName $fileName
     *
     * @throws FileImportException
     *
     * @return BaseLine
     */
    public function import(FileName $fileName): BaseLine
    {
        try {
            $baseLineData = $this->fileReader->readJsonFile($fileName);

            $historyMarkerAsString = ArrayUtils::getStringValue($baseLineData, BaseLine::HISTORY_MARKER);
            $analysisResultsAsArray = ArrayUtils::getArrayValue($baseLineData, BaseLine::ANALYSIS_RESULTS);
            $resultsParserName = ArrayUtils::getStringValue($baseLineData, BaseLine::RESULTS_PARSER);
            $historyAnalyserName = ArrayUtils::getStringValue($baseLineData, BaseLine::HISTORY_ANALYSER);

            $resultsParser = $this->resultsParserLookupService->getResultsParser($resultsParserName);
            $historyFactory = $this->historyFactoryLookupService->getHistoryFactory($historyAnalyserName);

            $historyMarker = $historyFactory->newHistoryMarkerFactory()->newHistoryMarker($historyMarkerAsString);
            $analysisResults = AnalysisResults::fromArray($analysisResultsAsArray);

            return new BaseLine($historyFactory, $analysisResults, $resultsParser, $historyMarker);
        } catch (ArrayParseException | FileAccessException | InvalidResultsParserException | InvalidHistoryFactoryException $e) {
            throw new FileImportException(BaseLine::BASE_LINE, $fileName, $e->getMessage());
        } catch (JsonParseException $e) {
            throw new FileImportException(BaseLine::BASE_LINE, $fileName, 'File not in JSON format');
        }
    }
}
