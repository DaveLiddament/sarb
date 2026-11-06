<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryMarkerException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GitCommitTest extends TestCase
{
    /**
     * @return array<string,array{string}>
     */
    public static function invalidGitCommitDataProvider(): array
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

            'sha256TooShort' => [
                'dccf9c8b8f2977cea0c1a1474d20531d9482a1c825b1a0d55163d54e29a3343',
            ],

            'sha256TooLong' => [
                'dccf9c8b8f2977cea0c1a1474d20531d9482a1c825b1a0d55163d54e29a3343a1',
            ],
        ];
    }

    #[DataProvider('invalidGitCommitDataProvider')]
    public function testValidateInvalidGitCommit(string $invalidCommit): void
    {
        $this->assertFalse(GitCommit::validateGitSha($invalidCommit));
    }

    #[DataProvider('invalidGitCommitDataProvider')]
    public function testInvalidGitCommit(string $invalidCommit): void
    {
        $this->expectException(InvalidHistoryMarkerException::class);
        new GitCommit($invalidCommit);
    }

    /**
     * @return array<int,array{string}>
     */
    public static function validGitCommitDataProvider(): array
    {
        return [
            [
                '462550d5644312b6757bb89ac85aa8d9590c28d7',
            ],
            [
                '30b68f53df4e590657ba5184b1c6013e9a8dad5c',
            ],
            [
                // SHA-256 object format
                'dccf9c8b8f2977cea0c1a1474d20531d9482a1c825b1a0d55163d54e29a3343a',
            ],
        ];
    }

    #[DataProvider('validGitCommitDataProvider')]
    public function testValidateValidGitCommit(string $commit): void
    {
        $this->assertTrue(GitCommit::validateGitSha($commit));
    }

    #[DataProvider('validGitCommitDataProvider')]
    public function testValidGitCommit(string $commit): void
    {
        $gitCommit = new GitCommit($commit);
        $this->assertEquals($commit, $gitCommit->asString());
    }
}
