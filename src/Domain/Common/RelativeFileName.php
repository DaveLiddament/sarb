<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

/**
 * FileName relative to ProjectRoot.
 */
class RelativeFileName extends FileName
{
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
    }
}
