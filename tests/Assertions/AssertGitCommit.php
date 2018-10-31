<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Assertions;

use DaveLiddament\StaticAnalysisBaseliner\Core\HistoryAnalyser\HistoryMarker;
use DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use PHPUnit\Framework\TestCase;

trait AssertGitCommit
{
    /**
     * Asserts HistoryMarker is of type GitCommit with the $expectedSha.
     *
     * @param string $expectedSha
     * @param HistoryMarker $actual
     */
    private function assertGitCommit(string $expectedSha, HistoryMarker $actual): void
    {
        TestCase::assertInstanceOf(GitCommit::class, $actual);
        TestCase::assertSame($expectedSha, $actual->asString());
    }
}
