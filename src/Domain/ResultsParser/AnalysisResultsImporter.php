<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

final class AnalysisResultsImporter
{
    /**
     * @throws AnalysisResultsImportException
     * @throws ErrorReportedByStaticAnalysisTool
     */
    public function import(
        ResultsParser $resultsParser,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString,
    ): AnalysisResults {
        try {
            return $resultsParser->convertFromString($analysisResultsAsString, $projectRoot);
        } catch (InvalidContentTypeException|ParseAtLocationException $e) {
            throw AnalysisResultsImportException::fromException($resultsParser->getIdentifier(), $e);
        }
    }
}
