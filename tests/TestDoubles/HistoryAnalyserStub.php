<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;

final class HistoryAnalyserStub implements HistoryAnalyser
{
    public function getPreviousLocation(RelativeFileName $fileName, LineNumber $lineNumber): PreviousLocation
    {
        return PreviousLocation::noPreviousLocation();
    }
}
