<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\ResultsPrunerInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\StringAssertionsTrait;
use PHPUnit\Framework\Assert;

class MockResultsPruner implements ResultsPrunerInterface
{
    use StringAssertionsTrait;

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
        string $analysisResults,
        ProjectRoot $projectRoot
    ): PrunedResults {
        if ($this->throwable) {
            throw $this->throwable;
        }
        $this->assertSameAllowingExtraNewLine($this->expectedAnalysisResults, $analysisResults);

        if ($this->projectRoot) {
            Assert::assertSame($this->projectRoot->__toString(), $projectRoot->__toString());
        }

        return $this->prunedOutputResults;
    }
}
