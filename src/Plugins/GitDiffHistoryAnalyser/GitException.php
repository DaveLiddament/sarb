<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\CommandFailedException;

class GitException extends HistoryAnalyserException
{
    public static function failedDiff(CommandFailedException $e): self
    {
        return new self("git-diff failed. {$e->getMessage()}");
    }

    public static function failedToGetSha(CommandFailedException $e): self
    {
        return new self("Failed to get current git SHA. {$e->getMessage()}");
    }
}
