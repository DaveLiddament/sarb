<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;

class HistoryAnalyserStub implements HistoryAnalyser
{
    public function getPreviousLocation(Location $location): PreviousLocation
    {
        return PreviousLocation::noPreviousLocation();
    }
}
