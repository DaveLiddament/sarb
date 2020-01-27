<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

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

    public function getHistoryFactory(): HistoryFactory
    {
        return $this->historyFactory;
    }

    public function getAnalysisResults(): AnalysisResults
    {
        return $this->analysisResults;
    }

    public function getResultsParser(): ResultsParser
    {
        return $this->resultsParser;
    }

    public function getHistoryMarker(): HistoryMarker
    {
        return $this->historyMarker;
    }
}
