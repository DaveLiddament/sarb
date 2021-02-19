<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use Webmozart\PathUtil\Path;

class AbsoluteFileName extends FileName
{
    /**
     * @throws InvalidPathException
     */
    public function __construct(string $fileName)
    {
        $isAbsolute = Path::isAbsolute($fileName);
        if (!$isAbsolute) {
            throw InvalidPathException::notAbsolutePath($fileName);
        }
        parent::__construct($fileName);
    }
}
