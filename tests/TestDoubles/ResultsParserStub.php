<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

class ResultsParserStub implements ResultsParser
{
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        return new AnalysisResults([]);
    }

    public function getIdentifier(): Identifier
    {
        return new ResultsParserStubIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return true;
    }
}
