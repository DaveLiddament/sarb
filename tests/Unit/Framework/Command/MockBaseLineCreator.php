<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Framework\Command;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Creator\BaseLineCreatorInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\StringAssertionsTrait;
use PHPUnit\Framework\Assert;

class MockBaseLineCreator implements BaseLineCreatorInterface
{
    use StringAssertionsTrait;

    public function __construct(
        private HistoryFactory $expectedHistoryFactory,
        private ResultsParser $expectedResultsParser,
        private BaseLineFileName $expectedBaseLineFileName,
        private ?ProjectRoot $expectedProjectRoot,
        private string $expectedAnaylsisResultsAsString,
        private ?\Throwable $throwable,
    ) {
    }

    public function createBaseLine(
        HistoryFactory $historyFactory,
        ResultsParser $resultsParser,
        BaseLineFileName $baselineFile,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString,
        bool $forceBaselineCreation,
    ): BaseLine {
        Assert::assertSame($this->expectedHistoryFactory, $historyFactory);
        Assert::assertSame($this->expectedResultsParser, $resultsParser);
        Assert::assertTrue($this->expectedBaseLineFileName->isEqual($baselineFile));
        if (null !== $this->expectedProjectRoot) {
            Assert::assertEquals(
                $this->expectedProjectRoot->getProjectRootDirectory(),
                $projectRoot->getProjectRootDirectory(),
            );
        }

        $this->assertSameAllowingExtraNewLine($this->expectedAnaylsisResultsAsString, $analysisResultsAsString);

        if (null !== $this->throwable) {
            throw $this->throwable;
        }

        $analysisResults = BaseLineAnalysisResults::fromArray([]);
        $historyMarker = new GitCommit('9cf13d75cdf3addb82f507b68f4990725748d7af');

        return new BaseLine($historyFactory, $analysisResults, $resultsParser, $historyMarker);
    }
}
