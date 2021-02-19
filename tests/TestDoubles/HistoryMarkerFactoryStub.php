<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;

class HistoryMarkerFactoryStub implements HistoryMarkerFactory
{
    public function newHistoryMarker(string $historyMarkerAsString): HistoryMarker
    {
        return new GitCommit('6b950d7ea0c49a43e1a909361d4f9bb8425d86a9');
    }

    public function newCurrentHistoryMarker(ProjectRoot $projectRoot): HistoryMarker
    {
        return new GitCommit('61d7f9833c128692c656160b2d21ed82e5358910');
    }
}
