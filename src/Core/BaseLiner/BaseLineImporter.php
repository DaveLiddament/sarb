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
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\StaticAnalysisResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Utils\JsonParseException;
use Webmozart\Assert\Assert;

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
     * BaseLineImporter constructor.
     *
     * @param FileReader $fileReader
     */
    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * Imports baseline results.
     *
     * @param StaticAnalysisResultsParser $analysisResultsParser
     * @param HistoryMarkerFactory $historyMarkerFactory
     * @param FileName $fileName
     *
     * @throws FileImportException
     *
     * @return BaseLine
     */
    public function import(
        StaticAnalysisResultsParser $analysisResultsParser,
        HistoryMarkerFactory $historyMarkerFactory,
        FileName $fileName
    ): BaseLine {
        try {
            $baseLineData = $this->fileReader->readJsonFile($fileName);

            $historyMarkerAsString = ArrayUtils::getStringValue($baseLineData, BaseLine::HISTORY_MARKER);
            $analysisResultsAsArray = ArrayUtils::getArrayValue($baseLineData, BaseLine::ANALYSIS_RESULTS);
            $expectedAnalyser = ArrayUtils::getStringValue($baseLineData, BaseLine::ANALYSER);

            $identifier = $analysisResultsParser->getIdentifier();
            $actualAnalyser = $identifier->getCode();

            Assert::same(
                $expectedAnalyser,
                $analysisResultsParser->getIdentifier()->getCode(),
                "Expected [$expectedAnalyser], got [$actualAnalyser]"
            );

            $historyMarker = $historyMarkerFactory->newHistoryMarker($historyMarkerAsString);
            $analysisResults = AnalysisResults::fromArray($analysisResultsAsArray);

            return new BaseLine($historyMarker, $analysisResults, $identifier);
        } catch (ArrayParseException | FileAccessException $e) {
            throw new FileImportException(BaseLine::BASE_LINE, $fileName, $e->getMessage());
        } catch (JsonParseException $e) {
            throw new FileImportException(BaseLine::BASE_LINE, $fileName, 'File not in JSON format');
        }
    }
}
