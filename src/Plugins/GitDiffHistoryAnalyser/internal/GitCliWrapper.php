<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use RuntimeException;
use Symfony\Component\Process\Process;

class GitCliWrapper implements GitWrapper
{
    /**
     * {@inheritdoc}
     */
    public function getCurrentSha(ProjectRoot $projectRoot): GitCommit
    {
        $gitCommand = $this->getGitCommand(['rev-parse', 'HEAD'], $projectRoot);
        $rawOutput = $this->runCommand($gitCommand, 'Failed to get SHA');
        $processOutput = trim($rawOutput);

        return new GitCommit($processOutput);
    }

    /**
     * {@inheritdoc}
     */
    public function getGitDiff(ProjectRoot $projectRoot, GitCommit $originalCommit): string
    {
        $arguments = [
            'diff',
            '-w',
            '-M',
            $originalCommit->asString(),
        ];
        $command = $this->getGitCommand($arguments, $projectRoot);

        return $this->runCommand($command, 'Failed to get git-diff');
    }

    /**
     * @param string[] $gitCommand
     */
    private function runCommand(array $gitCommand, string $context): string
    {
        $process = new Process($gitCommand);

        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        $exitCode = $process->getExitCode();

        $errorMessage = sprintf(
            '%s. Return code [%s] Error: %s',
            $context,
            null === $exitCode ? 'null' : (string) $exitCode,
            $process->getErrorOutput()
        );
        throw new RuntimeException($errorMessage);
    }

    /**
     * @param string[] $arguments
     *
     * @return string[]
     */
    private function getGitCommand(array $arguments, ProjectRoot $projectRoot): array
    {
        $gitCommand = [
            'git',
            '--git-dir='.$projectRoot.\DIRECTORY_SEPARATOR.'.git',
            "--work-tree={$projectRoot}",
        ];

        return array_merge($gitCommand, $arguments);
    }

    public function init(ProjectRoot $projectRoot): void
    {
        $command = [
            'git',
            'init',
            (string) $projectRoot,
        ];
        $this->runCommand($command, "git init {$projectRoot}");
    }

    public function addAndCommt(string $message, ProjectRoot $projectRoot): void
    {
        $addCommand = $this->getGitCommand(['add', '.'], $projectRoot);
        $this->runCommand($addCommand, 'Git add .');

        $commitCommand = $this->getGitCommand([
            '-c',
            'user.name=Anon',
            '-c',
            'user.email=anon@example.com',
            'commit',
            '-m',
            "$message",
        ], $projectRoot);
        $this->runCommand($commitCommand, 'git commit');
    }
}
