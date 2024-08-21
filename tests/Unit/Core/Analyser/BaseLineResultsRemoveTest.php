<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\BaseLineResultsRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\DiffHistoryAnalyser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\BaseLineResultsBuilder;
use PHPUnit\Framework\TestCase;

class BaseLineResultsRemoveTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    private const PROJECT_ROOT_PATH = '/home/sarb';
    private const FILE_1 = 'foo/file1.txt';
    private const FILE_1_FULL_PATH = self::PROJECT_ROOT_PATH.'/'.self::FILE_1;
    private const FILE_2 = 'foo/file2.txt';
    private const LINE_9 = 9;
    private const LINE_10 = 10;
    private const LINE_11 = 11;
    private const LINE_15 = 15;
    private const TYPE_1 = 'type1';
    private const TYPE_2 = 'type2';
    /**
     * @var DiffHistoryAnalyser
     */
    private $historyAnalyser;
    /**
     * @var BaseLineResultsRemover
     */
    private $baseLineResultsRemover;
    /**
     * @var AnalysisResults
     */
    private $latestAnalysisResults;
    /**
     * @var BaseLineAnalysisResults
     */
    private $baselineAnalysisResults;

    protected function setUp(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(self::PROJECT_ROOT_PATH);

        // Create baseline
        $baselineAnalysisResultsBuilder = new BaseLineResultsBuilder();
        $baselineAnalysisResultsBuilder->add(self::FILE_1, self::LINE_10, self::TYPE_1, Severity::error());
        $baselineAnalysisResultsBuilder->add(self::FILE_2, self::LINE_15, self::TYPE_2, Severity::error());

        // Create file mutations
        $fileMutationsBuilder = new FileMutationsBuilder();
        $fileMutationBuilder = new FileMutationBuilder($fileMutationsBuilder);
        $fileMutationBuilder->setOriginalFileName(new OriginalFileName(self::FILE_1));
        $fileMutationBuilder->setNewFileName(new NewFileName(self::FILE_1));
        $fileMutationBuilder->addLineMutation(LineMutation::newLineNumber(new LineNumber(self::LINE_9)));
        $fileMutationBuilder->build();
        $fileMutations = $fileMutationsBuilder->build();

        // Create latest results
        $latestAnalysisResultsBuilder = new AnalysisResultsBuilder();
        // This is in the baseline (it was line 10 in baseline)
        $this->addAnalysisResult($latestAnalysisResultsBuilder, $projectRoot, self::FILE_1_FULL_PATH, self::LINE_11, self::TYPE_1, Severity::error());
        // Added since baseline
        $this->addAnalysisResult($latestAnalysisResultsBuilder, $projectRoot, self::FILE_1_FULL_PATH, self::LINE_9, self::TYPE_2, Severity::warning());

        // Prune baseline results from latest results
        $this->historyAnalyser = new DiffHistoryAnalyser($fileMutations);
        $this->baseLineResultsRemover = new BaseLineResultsRemover();
        $this->latestAnalysisResults = $latestAnalysisResultsBuilder->build();
        $this->baselineAnalysisResults = $baselineAnalysisResultsBuilder->build();
    }

    public function testRemoveBaseLineResults(): void
    {
        $prunedAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
            $this->latestAnalysisResults,
            $this->historyAnalyser,
            $this->baselineAnalysisResults,
            false,
        );

        $actualResults = $prunedAnalysisResults->getAnalysisResults();

        // Assert results as expected
        // Of the original results:
        // the one in FILE_1 line 10 is now at FILE_1 line 11 (so should not appear in the pruned results)
        // the one in FILE_2 line 15 is not in latest results
        // A new bug has been introduced at FILE_1 line 9 (this is the only one that should appera in the results)
        $this->assertCount(1, $actualResults);

        $actualAnalysisResult = $actualResults[0];
        $expectedFileName = new AbsoluteFileName(self::FILE_1_FULL_PATH);
        $expectedLineNumber = new LineNumber(self::LINE_9);
        $expectedType = new Type(self::TYPE_2);

        $this->assertTrue($expectedFileName->isEqual($actualAnalysisResult->getLocation()->getAbsoluteFileName()));
        $this->assertTrue($expectedLineNumber->isEqual($actualAnalysisResult->getLocation()->getLineNumber()));
        $this->assertTrue($expectedType->isEqual($actualAnalysisResult->getType()));
    }

    public function testRemoveBaseLineResultsIngnoringWarnings(): void
    {
        $prunedAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
            $this->latestAnalysisResults,
            $this->historyAnalyser,
            $this->baselineAnalysisResults,
            true,
        );

        $actualResults = $prunedAnalysisResults->getAnalysisResults();

        $this->assertCount(0, $actualResults);
    }
}
