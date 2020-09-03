<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class AbsoluteFileName extends FileName
{
    public function __construct(string $fileName)
    {
        $isAbsolute = Path::isAbsolute($fileName);
        Assert::true($isAbsolute);
        parent::__construct($fileName);
    }
}
