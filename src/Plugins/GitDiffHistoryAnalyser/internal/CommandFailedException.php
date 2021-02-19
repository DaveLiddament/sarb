<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal;

use Exception;

class CommandFailedException extends Exception
{
    public static function newInstance(string $context, ?int $exitCode, string $errorMessage): self
    {
        $errorMessage = sprintf(
            '%s. Return code [%s] Error: %s',
            $context,
            null === $exitCode ? 'null' : (string) $exitCode,
            $errorMessage
        );

        return new self($errorMessage);
    }
}
