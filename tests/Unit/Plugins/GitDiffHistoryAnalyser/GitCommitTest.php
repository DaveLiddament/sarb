<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use PHPUnit\Framework\TestCase;

class GitCommitTest extends TestCase
{
    public function invalidGitCommitDataProvider(): array
    {
        return [
            'tooShort' => [
                '462550d5644312b6757bb89ac85aa8d9590c28d',
            ],

            'tooLong' => [
                '43462550d5644312b6757bb89ac85aa8d9590c28d',
            ],

            'invalidCharacters' => [
                '43462550d5644312b6757bb89ac85aa8d9590c2x',
            ],
        ];
    }

    /**
     * @param string $invalidCommit
     * @dataProvider invalidGitCommitDataProvider
     */
    public function testValidateInvalidGitCommit(string $invalidCommit): void
    {
        $this->assertFalse(GitCommit::validateGitSha($invalidCommit));
    }

    /**
     * @param string $invalidCommit
     * @dataProvider invalidGitCommitDataProvider
     */
    public function testInvalidGitCommit(string $invalidCommit): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GitCommit($invalidCommit);
    }

    public function validGitCommitDataProvider(): array
    {
        return [
            [
                '462550d5644312b6757bb89ac85aa8d9590c28d7',
            ],
            [
                '30b68f53df4e590657ba5184b1c6013e9a8dad5c',
            ],
        ];
    }

    /**
     * @param string $commit
     * @dataProvider validGitCommitDataProvider
     */
    public function testValidateValidGitCommit(string $commit): void
    {
        $this->assertTrue(GitCommit::validateGitSha($commit));
    }

    /**
     * @param string $commit
     * @dataProvider validGitCommitDataProvider
     */
    public function testValidGitCommit(string $commit): void
    {
        $gitCommit = new GitCommit($commit);
        $this->assertEquals($commit, $gitCommit->asString());
    }
}
