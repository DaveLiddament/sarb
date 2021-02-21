<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryAnalyserException;

class GitNotCleanException extends HistoryAnalyserException
{
    private const MESSAGE = <<<TEXT
There are modified or new files (to see them run: git status).
Either commit these or if the modified/new files have no impact the static analysers output rerun this command with -f flag.

SARB pins the baseline current git SHA, as files are some modified/new files the current SHA it not representative of the current state of the codebase.
TEXT;

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
