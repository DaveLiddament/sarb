<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\ResultsPrunerInterface;
use PHPUnit\Framework\Assert;

class MockResultsPruner implements ResultsPrunerInterface
{
    /**
     * @var FileName
     */
    private $expectedBaseLineFileName;
    /**
     * @var string
     */
    private $expectedAnalysisResults;
    /**
     * @var PrunedResults
     */
    private $prunedOutputResults;
    /**
     * @var ProjectRoot|null
     */
    private $projectRoot;
    /**
     * @var \Throwable|null
     */
    private $throwable;

    public function __construct(
        FileName $expectedBaseLineFileName,
        string $expectedAnalysisResults,
        PrunedResults $prunedOutputResults,
        ?ProjectRoot $projectRoot,
        ?\Throwable $throwable
    ) {
        $this->expectedBaseLineFileName = $expectedBaseLineFileName;
        $this->expectedAnalysisResults = $expectedAnalysisResults;
        $this->prunedOutputResults = $prunedOutputResults;
        $this->projectRoot = $projectRoot;
        $this->throwable = $throwable;
    }

    public function getPrunedResults(
        FileName $baseLineFileName,
        string $analaysisResults,
        ProjectRoot $projectRoot
    ): PrunedResults {
        if ($this->throwable) {
            throw $this->throwable;
        }

        Assert::assertSame($this->expectedAnalysisResults, $analaysisResults);

        if ($this->projectRoot) {
            Assert::assertSame($this->projectRoot->__toString(), $projectRoot->__toString());
        }

        return $this->prunedOutputResults;
    }
}
