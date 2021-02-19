<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

class InvalidHistoryMarkerException extends SarbException
{
    public static function invalidHistoryMarker(string $message): self
    {
        return new self($message);
    }
}
