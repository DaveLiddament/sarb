<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;

class GitNotCleanException extends HistoryAnalyserException
{
    private const MESSAGE = <<<TEXT
There are modified or new files. To see them run:
 
 git status


SARB pins the baseline current git SHA, as files are some modified/new files the current SHA it not representative of the current state of the codebase.

To fix there are 2 choices:

1. Commit these modified/new files
2. If the modified/new files have no impact on the static analyser's output rerun this command with -f flag.

TEXT;

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
