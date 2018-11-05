<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\FileMutations;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitHistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal\StubGitWrapper;

class StubHistoryFactory implements HistoryFactory
{
    /**
     * @var FileMutations
     */
    private $fileMutations;

    /**
     * StubHistoryFactory constructor.
     *
     * @param FileMutations $fileMutations
     */
    public function __construct(FileMutations $fileMutations)
    {
        $this->fileMutations = $fileMutations;
    }

    /**
     * {@inheritdoc}
     */
    public function newHistoryAnalyser(HistoryMarker $historyMarker): HistoryAnalyser
    {
        return new DiffHistoryAnalyser($this->fileMutations);
    }

    /**
     * {@inheritdoc}
     */
    public function newHistoryMarkerFactory(): HistoryMarkerFactory
    {
        return new GitHistoryMarkerFactory(new StubGitWrapper(StubGitWrapper::GIT_SHA_1, ''));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'stub';
    }

    /**
     * {@inheritdoc}
     */
    public function setProjectRoot(?string $projectRoot): void
    {
        // Nothing to do.
    }
}
