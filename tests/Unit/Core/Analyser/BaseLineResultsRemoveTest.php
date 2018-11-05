<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\BaseLineResultsRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser\PsalmJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal\StubGitWrapper;
use PHPUnit\Framework\TestCase;

class BaseLineResultsRemoveTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    private const FILE_1 = 'foo/file1.txt';
    private const FILE_2 = 'foo/file2.txt';
    private const LINE_9 = 9;
    private const LINE_10 = 10;
    private const LINE_11 = 11;
    private const LINE_15 = 15;
    private const TYPE_1 = 'type1';
    private const TYPE_2 = 'type2';

    public function testRemoveBaseLineResults(): void
    {
        // Create file mutations
        $fileMutationsBuilder = new FileMutationsBuilder();
        $fileMutationBuilder = new FileMutationBuilder($fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName(new OriginalFileName(self::FILE_1));
        $fileMutationBuilder->setNewFileName(new NewFileName(self::FILE_1));
        $fileMutationBuilder->addLineMutation(LineMutation::newLineNumber(new LineNumber(self::LINE_9)));
        $fileMutationBuilder->build();
        $fileMutations = $fileMutationsBuilder->build();

        // Create baseline
        $historyMarker = new GitCommit(StubGitWrapper::GIT_SHA_1);
        $baselineAnalysisResults = new AnalysisResults();
        $this->addAnalysisResult($baselineAnalysisResults, self::FILE_1, self::LINE_10, self::TYPE_1);
        $this->addAnalysisResult($baselineAnalysisResults, self::FILE_2, self::LINE_15, self::TYPE_2);

        $resultsParser = new PsalmJsonResultsParser();
        $historyFactory = new StubHistoryFactory($fileMutations);
        $baseLine = new BaseLine($historyFactory, $baselineAnalysisResults, $resultsParser, $historyMarker);

        // Create latest results
        $latestAnalysisResults = new AnalysisResults();
        // This is in the baseline (it was line 10 in baseline)
        $this->addAnalysisResult($latestAnalysisResults, self::FILE_1, self::LINE_11, self::TYPE_1);
        // Added since baseline
        $this->addAnalysisResult($latestAnalysisResults, self::FILE_1, self::LINE_9, self::TYPE_2);

        // Prune baseline results from latest results
        $historyAnalyser = new DiffHistoryAnalyser($fileMutations);
        $baseLineResultsRemover = new BaseLineResultsRemover();
        $prunedAnalysisResults = $baseLineResultsRemover->pruneBaseLine($latestAnalysisResults, $baseLine);

        $actualResults = $prunedAnalysisResults->getAnalysisResults();

        // Assert results as expected
        // Of the original results:
        // the one in FILE_1 line 10 is now at FILE_1 line 11 (so should not appear in the pruned results)
        // the one in FILE_2 line 15 is not in latest results
        // A new bug has been introduced at FILE_1 line 9 (this is the only one that should appera in the results)
        $this->assertCount(1, $actualResults);

        $actualAnalysisResult = $actualResults[0];
        $expectedLocation = new Location(new FileName(self::FILE_1), new LineNumber(self::LINE_9));
        $expectedType = new Type(self::TYPE_2);

        $this->assertTrue($expectedLocation->isEqual($actualAnalysisResult->getLocation()));
        $this->assertTrue($expectedType->isEqual($actualAnalysisResult->getType()));
    }
}
