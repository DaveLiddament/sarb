<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarkerFactory;

class HistoryFactoryStub implements HistoryFactory
{
    public const CODE = 'history-factory-stub';

    public function newHistoryAnalyser(HistoryMarker $baseLineHistoryMarker, ProjectRoot $projectRoot): HistoryAnalyser
    {
        return new HistoryAnalyserStub();
    }

    public function newHistoryMarkerFactory(): HistoryMarkerFactory
    {
        return new HistoryMarkerFactoryStub();
    }

    public function getIdentifier(): string
    {
        return self::CODE;
    }
}
