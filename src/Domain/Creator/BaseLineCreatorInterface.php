<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Creator;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ErrorReportedByStaticAnalysisTool;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

interface BaseLineCreatorInterface
{
    /**
     * @throws FileAccessException
     * @throws BaseLineImportException
     * @throws AnalysisResultsImportException
     * @throws HistoryAnalyserException
     * @throws ErrorReportedByStaticAnalysisTool
     */
    public function createBaseLine(
        HistoryFactory $historyFactory,
        ResultsParser $resultsParser,
        BaseLineFileName $baselineFile,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString,
        bool $forceBaselineCreation,
    ): BaseLine;
}
