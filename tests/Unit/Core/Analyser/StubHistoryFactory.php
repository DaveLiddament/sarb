<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\FileMutations;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitHistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal\StubGitWrapper;

final class StubHistoryFactory implements HistoryFactory
{
    /**
     * StubHistoryFactory constructor.
     */
    public function __construct(
        private FileMutations $fileMutations,
    ) {
    }

    public function newHistoryAnalyser(HistoryMarker $baseLineHistoryMarker, ProjectRoot $projectRoot): HistoryAnalyser
    {
        return new DiffHistoryAnalyser($this->fileMutations);
    }

    public function newHistoryMarkerFactory(): HistoryMarkerFactory
    {
        return new GitHistoryMarkerFactory(new StubGitWrapper(StubGitWrapper::GIT_SHA_1, ''));
    }

    public function getIdentifier(): string
    {
        return 'stub';
    }
}
