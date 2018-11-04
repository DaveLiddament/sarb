<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\SarbException;

class FileAccessException extends SarbException
{
    public static function readFileException(): self
    {
        return new self('Failed to read file (does it exist with correct permissions?)');
    }

    public static function writeFileException(): self
    {
        return new self('Failed to write file (are permissions correct?)');
    }
}
