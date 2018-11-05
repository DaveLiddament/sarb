<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitWrapper;
use Webmozart\Assert\Assert;

class GitDiffHistoryFactory implements HistoryFactory
{
    /**
     * @var GitWrapper
     */
    private $gitCliWrapper;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * GitDiffHistoryFactory constructor.
     *
     * @param GitWrapper $gitCliWrapper
     * @param Parser $parser
     */
    public function __construct(GitWrapper $gitCliWrapper, Parser $parser)
    {
        $this->gitCliWrapper = $gitCliWrapper;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function newHistoryAnalyser(HistoryMarker $historyMarker): HistoryAnalyser
    {
        Assert::isInstanceOf($historyMarker, GitCommit::class);
        $newDiff = $this->gitCliWrapper->getCurrentSha();
        $diff = $this->gitCliWrapper->getGitDiff($historyMarker, $newDiff);
        $fileMutations = $this->parser->parseDiff($diff);

        return new DiffHistoryAnalyser($fileMutations);
    }

    /**
     * {@inheritdoc}
     */
    public function newHistoryMarkerFactory(): HistoryMarkerFactory
    {
        return new GitHistoryMarkerFactory($this->gitCliWrapper);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'git';
    }

    /**
     * {@inheritdoc}
     */
    public function setProjectRoot(?string $projectRoot): void
    {
        $this->gitCliWrapper->setProjectRoot($projectRoot);
    }
}
