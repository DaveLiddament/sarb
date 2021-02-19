<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

/**
 * Holds path to the baseline file.
 */
class BaseLineFileName extends FileName
{
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
    }
}
