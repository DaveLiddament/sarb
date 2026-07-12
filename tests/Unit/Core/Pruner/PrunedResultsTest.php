<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\BaseLineResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles\HistoryFactoryStub;
use PHPUnit\Framework\TestCase;

final class PrunedResultsTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    /**
     * @var AnalysisResult
     */
    private $baseLinedResult;

    /**
     * @var AnalysisResult
     */
    private $newResult;

    protected function setUp(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $this->baseLinedResult = $this->buildAnalysisResult($projectRoot, '/FILE_1', 1, 'TYPE_1', Severity::error());
        $this->newResult = $this->buildAnalysisResult($projectRoot, '/FILE_2', 2, 'TYPE_2', Severity::error());
    }

    public function testGetters(): void
    {
        $baseLine = $this->createBaseLine();
        $prunedAnalysisResults = new AnalysisResults([$this->newResult]);
        $inputAnalysisResults = new AnalysisResults([$this->baseLinedResult, $this->newResult]);

        $prunedResults = new PrunedResults($baseLine, $prunedAnalysisResults, $inputAnalysisResults);

        $this->assertSame($baseLine, $prunedResults->getBaseLine());
        $this->assertSame($prunedAnalysisResults, $prunedResults->getPrunedResults());
        $this->assertSame($inputAnalysisResults, $prunedResults->getInputAnalysisResults());
    }

    public function testBaseLinedResultsAreInputResultsMinusPrunedResults(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(),
            new AnalysisResults([$this->newResult]),
            new AnalysisResults([$this->baseLinedResult, $this->newResult]),
        );

        $baseLinedResults = $prunedResults->getBaseLinedResults();

        $this->assertSame([$this->baseLinedResult], $baseLinedResults->getAnalysisResults());
    }

    public function testNoBaseLinedResultsWhenAllInputResultsAreNew(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(),
            new AnalysisResults([$this->baseLinedResult, $this->newResult]),
            new AnalysisResults([$this->baseLinedResult, $this->newResult]),
        );

        $this->assertTrue($prunedResults->getBaseLinedResults()->hasNoIssues());
    }

    private function createBaseLine(): BaseLine
    {
        $baseLineResultsBuilder = new BaseLineResultsBuilder();
        $baseLineResultsBuilder->add('FILE_1', 1, 'TYPE_1', Severity::error());

        return new BaseLine(
            new HistoryFactoryStub(),
            $baseLineResultsBuilder->build(),
            new SarbJsonResultsParser(),
            new GitCommit('fae40b3d596780ffd746dbd2300d05dcfbd09033'),
        );
    }
}
