<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactoryLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryFactoryException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParserLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;

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
     * @throws FileImportException
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
