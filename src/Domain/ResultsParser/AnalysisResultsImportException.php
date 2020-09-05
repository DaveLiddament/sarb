<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use Exception;

/**
 * Used to for when importing a BaseLine file fails.
 */
class AnalysisResultsImportException extends SarbException
{
    public static function fromException(Identifier $identifier, Exception $e): self
    {
        $message = <<<EOF
Failed to parse analysis results. Have you supplied the data for format [{$identifier->getCode()}].
{$e->getMessage()}
EOF;

        return new self($message, 0, $e);
    }
}
