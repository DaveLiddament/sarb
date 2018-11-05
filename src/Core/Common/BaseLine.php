<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\ResultsParser;

class BaseLine
{
    public const HISTORY_MARKER = 'historyMarker';
    public const HISTORY_ANALYSER = 'historyAnalyser';
    public const ANALYSIS_RESULTS = 'analysisResults';
    public const RESULTS_PARSER = 'resultsParser';
    public const BASE_LINE = 'SARB BaseLine';

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var ResultsParser
     */
    private $resultsParser;

    /**
     * @var HistoryMarker
     */
    private $historyMarker;

    /**
     * BaseLine constructor.
     *
     * @param HistoryFactory $historyFactory
     * @param AnalysisResults $analysisResults
     * @param ResultsParser $resultsParser
     * @param HistoryMarker $historyMarker
     */
    public function __construct(
        HistoryFactory $historyFactory,
        AnalysisResults $analysisResults,
        ResultsParser $resultsParser,
        HistoryMarker $historyMarker
    ) {
        $this->historyFactory = $historyFactory;
        $this->analysisResults = $analysisResults;
        $this->resultsParser = $resultsParser;
        $this->historyMarker = $historyMarker;
    }

    /**
     * @return HistoryFactory
     */
    public function getHistoryFactory(): HistoryFactory
    {
        return $this->historyFactory;
    }

    /**
     * @return AnalysisResults
     */
    public function getAnalysisResults(): AnalysisResults
    {
        return $this->analysisResults;
    }

    /**
     * @return ResultsParser
     */
    public function getResultsParser(): ResultsParser
    {
        return $this->resultsParser;
    }

    /**
     * @return HistoryMarker
     */
    public function getHistoryMarker(): HistoryMarker
    {
        return $this->historyMarker;
    }
}
