<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;

class HistoryAnalyserStub implements HistoryAnalyser
{
    public function getPreviousLocation(FileName $fileName, LineNumber $lineNumber): PreviousLocation
    {
        return PreviousLocation::noPreviousLocation();
    }
}
