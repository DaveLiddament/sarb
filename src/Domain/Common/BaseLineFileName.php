<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

/**
 * Holds path to the baseline file.
 */
final class BaseLineFileName extends FileName
{
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
    }
}
