<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Core\File;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\SarbException;

/**
 * Used when one of the files being imported is invalid.
 */
class FileImportException extends SarbException
{
    public function __construct(string $expectedFileType, FileName $fileName, string $problem)
    {
        $message = sprintf(
            'Attempting to parse file of type [%s]. Have you specified correct filename? Filename supplied: [%s]. Problem: %s',
            $expectedFileType,
            $fileName->getFileName(),
            $problem
        );
        parent::__construct($message);
    }
}
