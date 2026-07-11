<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

final class InvalidHistoryMarkerException extends SarbException
{
    public static function invalidHistoryMarker(string $message): self
    {
        return new self($message);
    }
}
