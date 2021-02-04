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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryMarkerException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitException;
use LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class GitCliWrapper implements GitWrapper
{
    public function getCurrentSha(ProjectRoot $projectRoot): GitCommit
    {
        try {
            $gitCommand = $this->getGitCommand(['rev-parse', 'HEAD'], $projectRoot);
            $rawOutput = $this->runCommand($gitCommand, 'Failed to get SHA');
            $processOutput = trim($rawOutput);

            return new GitCommit($processOutput);
        } catch (CommandFailedException $e) {
            throw GitException::failedToGetSha($e);
        } catch (InvalidHistoryMarkerException $e) { // @codeCoverageIgnore
            // This should never happen as git SHA got from running git command will always return valid SHA.
            throw new LogicException('Invalid git SHA '.$e->getMessage()); // @codeCoverageIgnore
        }
    }

    public function getGitDiff(ProjectRoot $projectRoot, GitCommit $originalCommit): string
    {
        $arguments = [
            'diff',
            '-w',
            '-M',
            $originalCommit->asString(),
        ];
        $command = $this->getGitCommand($arguments, $projectRoot);

        try {
            return $this->runCommand($command, 'Failed to get git-diff');
        } catch (CommandFailedException $e) {
            throw GitException::failedDiff($e);
        }
    }

    /**
     * @param string[] $gitCommand
     *
     * @throws CommandFailedException
     */
    private function runCommand(array $gitCommand, string $context): string
    {
        try {
            $process = new Process($gitCommand);
            $process->run();
        } catch (RuntimeException $e) { // @codeCoverageIgnore
            // // Impossible to simulate this happening
            throw CommandFailedException::newInstance($context, null, $e->getMessage()); // @codeCoverageIgnore
        }

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        try {
            $exitCode = $process->getExitCode();
        } catch (RuntimeException $e) { // @codeCoverageIgnore
            // Impossible to simulate this happening
            $exitCode = null; // @codeCoverageIgnore
        }

        throw CommandFailedException::newInstance($context, $exitCode, $process->getErrorOutput());
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

    /**
     * Only used for testing.
     *
     * @throws CommandFailedException
     */
    public function init(ProjectRoot $projectRoot): void
    {
        $command = [
            'git',
            'init',
            (string) $projectRoot,
        ];
        $this->runCommand($command, "git init {$projectRoot}");
    }

    /**
     * Only used for testing.
     *
     * @throws CommandFailedException
     */
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
