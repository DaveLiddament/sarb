<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Pruner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\TypeIdentifiersUsage;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\BaseLineResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles\HistoryFactoryStub;
use PHPUnit\Framework\TestCase;

final class PrunedResultsTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    public function testGetters(): void
    {
        $baseLine = $this->createBaseLine(null, 1);
        $prunedAnalysisResults = new AnalysisResults([]);
        $inputAnalysisResults = $this->createInputResults(false);

        $prunedResults = new PrunedResults($baseLine, $prunedAnalysisResults, $inputAnalysisResults);

        $this->assertSame($baseLine, $prunedResults->getBaseLine());
        $this->assertSame($prunedAnalysisResults, $prunedResults->getPrunedResults());
        $this->assertSame($inputAnalysisResults, $prunedResults->getInputAnalysisResults());
    }

    public function testRecommendRegeneratingWhenLegacyBaseLineAndInputHasTypeIdentifiers(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(null, 1),
            new AnalysisResults([]),
            $this->createInputResults(true),
        );

        $this->assertTrue($prunedResults->shouldRecommendRegeneratingBaseLine());
    }

    public function testNoRecommendationWhenInputHasNoTypeIdentifiers(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(null, 1),
            new AnalysisResults([]),
            $this->createInputResults(false),
        );

        $this->assertFalse($prunedResults->shouldRecommendRegeneratingBaseLine());
    }

    public function testNoRecommendationWhenBaseLineIsEmpty(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(null, 0),
            new AnalysisResults([]),
            $this->createInputResults(true),
        );

        $this->assertFalse($prunedResults->shouldRecommendRegeneratingBaseLine());
    }

    public function testNoRecommendationWhenBaseLineUsesTypeIdentifiers(): void
    {
        $prunedResults = new PrunedResults(
            $this->createBaseLine(TypeIdentifiersUsage::all(), 1),
            new AnalysisResults([]),
            $this->createInputResults(true),
        );

        $this->assertFalse($prunedResults->shouldRecommendRegeneratingBaseLine());
    }

    private function createBaseLine(?TypeIdentifiersUsage $typeIdentifiersUsage, int $resultCount): BaseLine
    {
        $baseLineResultsBuilder = new BaseLineResultsBuilder();
        for ($i = 0; $i < $resultCount; ++$i) {
            $baseLineResultsBuilder->add('file1', $i + 1, "type$i", Severity::error());
        }

        return new BaseLine(
            new HistoryFactoryStub(),
            $baseLineResultsBuilder->build(),
            new SarbJsonResultsParser(),
            new GitCommit('fae40b3d596780ffd746dbd2300d05dcfbd09033'),
            $typeIdentifiersUsage,
        );
    }

    private function createInputResults(bool $withLegacyType): AnalysisResults
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $analysisResultsBuilder = new AnalysisResultsBuilder();
        $this->addAnalysisResult(
            $analysisResultsBuilder,
            $projectRoot,
            '/FILE_1',
            1,
            'TYPE_1',
            Severity::error(),
            $withLegacyType ? 'LEGACY_TYPE_1' : null,
        );

        return $analysisResultsBuilder->build();
    }
}
