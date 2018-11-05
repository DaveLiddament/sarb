<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser;

interface ResultsParserLookupService
{
    /**
     * Returns ResultsParser of the given name.
     *
     * @param string $name
     *
     * @throws InvalidResultsParserException
     *
     * @return StaticAnalysisResultsParser
     */
    public function getResultsParser(string $name): StaticAnalysisResultsParser;
}
