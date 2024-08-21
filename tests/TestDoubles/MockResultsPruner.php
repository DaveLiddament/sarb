<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\ResultsPrunerInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\StringAssertionsTrait;
use PHPUnit\Framework\Assert;

class MockResultsPruner implements ResultsPrunerInterface
{
    use StringAssertionsTrait;

    public function __construct(
        private string $expectedAnalysisResults,
        private PrunedResults $prunedOutputResults,
        private ?ProjectRoot $projectRoot,
        private ?\Throwable $throwable,
    ) {
    }

    public function getPrunedResults(
        BaseLineFileName $baseLineFileName,
        string $analysisResults,
        ProjectRoot $projectRoot,
        bool $ignoreWarnings,
    ): PrunedResults {
        if (null !== $this->throwable) {
            throw $this->throwable;
        }
        $this->assertSameAllowingExtraNewLine($this->expectedAnalysisResults, $analysisResults);

        if (null !== $this->projectRoot) {
            Assert::assertSame($this->projectRoot->getProjectRootDirectory(), $projectRoot->getProjectRootDirectory());
        }

        return $this->prunedOutputResults;
    }
}
