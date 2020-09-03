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
use Throwable;

class MockBaseLineCreator implements BaseLineCreatorInterface
{
    use StringAssertionsTrait;

    /**
     * @var HistoryFactory
     */
    private $expectedHistoryFactory;
    /**
     * @var ResultsParser
     */
    private $expectedResultsParser;
    /**
     * @var BaseLineFileName
     */
    private $expectedBaseLineFileName;
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
        BaseLineFileName $expectedBaseLineFileName,
        ?ProjectRoot $expectedProjectRoot,
        string $expectedAnaylsisResutlsAsString,
        ?Throwable $throwable
    ) {
        $this->expectedHistoryFactory = $expectedHistoryFactory;
        $this->expectedResultsParser = $expectedResultsParser;
        $this->expectedBaseLineFileName = $expectedBaseLineFileName;
        $this->expectedProjectRoot = $expectedProjectRoot;
        $this->expectedAnaylsisResultsAsString = $expectedAnaylsisResutlsAsString;
        $this->throwable = $throwable;
    }

    public function createBaseLine(
        HistoryFactory $historyFactory,
        ResultsParser $resultsParser,
        BaseLineFileName $baselineFile,
        ProjectRoot $projectRoot,
        string $analysisResultsAsString
    ): BaseLine {
        Assert::assertSame($this->expectedHistoryFactory, $historyFactory);
        Assert::assertSame($this->expectedResultsParser, $resultsParser);
        Assert::assertTrue($this->expectedBaseLineFileName->isEqual($baselineFile));
        if (null !== $this->expectedProjectRoot) {
            Assert::assertEquals($this->expectedProjectRoot->__toString(), $projectRoot->__toString());
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
