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

use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use RuntimeException;
use Symfony\Component\Process\Process;

class GitCliWrapper implements GitWrapper
{
    /**
     * @var string|null
     */
    private $projectRoot;

    /**
     * {@inheritdoc}
     */
    public function getCurrentSha(): GitCommit
    {
        $gitCommand = $this->getGitCommand('rev-parse HEAD');
        $rawOutput = $this->runCommand($gitCommand, 'Failed to get SHA');
        $processOutput = trim($rawOutput);

        return new GitCommit($processOutput);
    }

    /**
     * {@inheritdoc}
     */
    public function getGitDiff(GitCommit $originalCommit, GitCommit $newCommit): string
    {
        $arguments = sprintf('diff -w -M %s..%s', $originalCommit->asString(), $newCommit->asString());
        $command = $this->getGitCommand($arguments);

        return $this->runCommand($command, 'Failed to get git-diff');
    }

    /**
     * {@inheritdoc}
     */
    public function setProjectRoot(?string $projectRoot): void
    {
        $this->projectRoot = $projectRoot;
    }

    private function runCommand(string $gitCommand, string $context): string
    {
        $process = new Process($gitCommand);

        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        $errorMessage = sprintf(
            '%s. Return code [%d] Error: %s',
            $context,
            $process->getExitCode(),
            $process->getErrorOutput()
        );
        throw new RuntimeException($errorMessage);
    }

    private function getGitCommand(string $arguments): string
    {
        $projectRootConfig = '';
        if (null !== $this->projectRoot) {
            $projectRootConfig = "--git-dir=\"{$this->projectRoot}\"/.git --work-tree=\"{$this->projectRoot}\"";
        }

        return "git {$projectRootConfig} {$arguments} ";
    }

    public function init(): void
    {
        $command = 'git init ';
        if (null !== $this->projectRoot) {
            $command .= $this->projectRoot;
        }
        $this->runCommand($command, $command);
    }

    public function addAndCommt(string $message): void
    {
        $addCommand = $this->getGitCommand('add .');
        $this->runCommand($addCommand, $addCommand);

        $commitCommand = $this->getGitCommand("-c \"user.name=Anon\" -c \"user.email=anon@example.com\" commit -m \"$message\"");
        $this->runCommand($commitCommand, $commitCommand);
    }
}
