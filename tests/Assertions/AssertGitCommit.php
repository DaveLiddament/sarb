<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Assertions;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use PHPUnit\Framework\TestCase;

trait AssertGitCommit
{
    /**
     * Asserts HistoryMarker is of type GitCommit with the $expectedSha.
     */
    private function assertGitCommit(string $expectedSha, HistoryMarker $actual): void
    {
        TestCase::assertInstanceOf(GitCommit::class, $actual);
        TestCase::assertSame($expectedSha, $actual->asString());
    }
}
