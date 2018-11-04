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

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\Identifier;

class BaseLine
{
    public const HISTORY_MARKER = 'historyMarker';
    public const ANALYSIS_RESULTS = 'analysisResults';
    public const ANALYSER = 'analyser';
    const BASE_LINE = 'SARB BaseLine';

    /**
     * @var HistoryMarker
     */
    private $historyMarker;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var Identifier
     */
    private $identifier;

    /**
     * BaseLine constructor.
     *
     * @param HistoryMarker $historyMarker
     * @param AnalysisResults $analysisResults
     * @param Identifier $identifier
     */
    public function __construct(HistoryMarker $historyMarker, AnalysisResults $analysisResults, Identifier $identifier)
    {
        $this->historyMarker = $historyMarker;
        $this->analysisResults = $analysisResults;
        $this->identifier = $identifier;
    }

    /**
     * @return HistoryMarker
     */
    public function getHistoryMarker(): HistoryMarker
    {
        return $this->historyMarker;
    }

    /**
     * @return AnalysisResults
     */
    public function getAnalysisResults(): AnalysisResults
    {
        return $this->analysisResults;
    }

    /**
     * @return Identifier
     */
    public function getIdentifier(): Identifier
    {
        return $this->identifier;
    }
}
