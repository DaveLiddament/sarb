<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

final class FileAccessException extends SarbException
{
    public static function readFileException(FileName $fileName): self
    {
        return new self("Failed to read file [{$fileName->getFileName()}] (does it exist with correct permissions?)");
    }

    public static function writeFileException(FileName $fileName): self
    {
        return new self("Failed to write file [{$fileName->getFileName()}] (are permissions correct?)");
    }
}
