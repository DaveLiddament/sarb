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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarkerFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\Parser;
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
     */
    public function __construct(GitWrapper $gitCliWrapper, Parser $parser)
    {
        $this->gitCliWrapper = $gitCliWrapper;
        $this->parser = $parser;
    }

    /**
     * @throws HistoryAnalyserException
     */
    public function newHistoryAnalyser(HistoryMarker $baseLineHistoryMarker, ProjectRoot $projectRoot): HistoryAnalyser
    {
        Assert::isInstanceOf($baseLineHistoryMarker, GitCommit::class);
        $diff = $this->gitCliWrapper->getGitDiff($projectRoot, $baseLineHistoryMarker);
        $fileMutations = $this->parser->parseDiff($diff);

        return new DiffHistoryAnalyser($fileMutations);
    }

    public function newHistoryMarkerFactory(): HistoryMarkerFactory
    {
        return new GitHistoryMarkerFactory($this->gitCliWrapper);
    }

    public function getIdentifier(): string
    {
        return 'git';
    }
}
