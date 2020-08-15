<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Framework\Command;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Creator\BaseLineCreatorInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use PHPUnit\Framework\Assert;
use Throwable;

class MockBaseLineCreator implements BaseLineCreatorInterface
{
    /**
     * @var HistoryFactory
     */
    private $expectedHistoryFactory;
    /**
     * @var ResultsParser
     */
    private $expectedResultsParser;
    /**
     * @var FileName
     */
    private $expectedFileName;
    /**
     * @var ProjectRoot|null
     */
    private $expectedProjectRoot;
    /**
     * @var string
     */
    private $expectedAnaylsisResultsAsString;
    /**
     * @var Throwable|null
     */
    private $throwable;

    public function __construct(
        HistoryFactory $expectedHistoryFactory,
        ResultsParser $expectedResultsParser,
        FileName $expectedFileName,
        ?ProjectRoot $expectedProjectRoot,
        string $expectedAnaylsisResutlsAsString,
        ?Throwable $throwable
    ) {
        $this->expectedHistoryFactory = $expectedHistoryFactory;
        $this->expectedResultsParser = $expectedResultsParser;
        $this->expectedFileName = $expectedFileName;
        $this->expectedProjectRoot = $expectedProjectRoot;
        $this->expectedAnaylsisResultsAsString = $expectedAnaylsisResutlsAsString;
        $this->throwable = $throwable;
    }

    public function createBaseLine(
        HistoryFactory $historyFactory,
        ResultsParser $resultsParser,
        FileName $baselineFile,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString
    ): BaseLine {
        Assert::assertSame($this->expectedHistoryFactory, $historyFactory);
        Assert::assertSame($this->expectedResultsParser, $resultsParser);
        Assert::assertTrue($this->expectedFileName->isEqual($baselineFile));
        if ($this->expectedProjectRoot) {
            Assert::assertEquals($this->expectedProjectRoot->__toString(), $projectRoot->__toString());
        }
        Assert::assertSame($this->expectedAnaylsisResultsAsString, $analysisResultsAsString);

        if ($this->throwable) {
            throw $this->throwable;
        }

        $analysisResults = new AnalysisResults();
        $historyMarker = new GitCommit('9cf13d75cdf3addb82f507b68f4990725748d7af');

        return new BaseLine($historyFactory, $analysisResults, $resultsParser, $historyMarker);
    }
}
