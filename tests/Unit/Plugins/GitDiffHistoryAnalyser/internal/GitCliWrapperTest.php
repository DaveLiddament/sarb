<?php

declare(strict_types=1);


namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser\internal;


use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GitCliWrapperTest extends TestCase
{

    public function testGitCommandFails(): void
    {
        $projectRoot = new ProjectRoot(__DIR__, __DIR__);
        $gitCliWrapper = new GitCliWrapper();
        $this->expectException(RuntimeException::class);
        $gitCliWrapper->getCurrentSha($projectRoot);
    }
}
