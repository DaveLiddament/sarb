<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

class JsonParseException extends SarbException
{
    public static function invalidJsonFile(FileName $fileName): self
    {
        return new self("File {$fileName->getFileName()} does not contain valid JSON");
    }

    public static function invalidJsonString(string $json): self
    {
        return new self("Invalid JSON [$json]");
    }

    public static function invalidDataToConvertToJsonString(): self
    {
        return new self('Can not convert data to JSON string');
    }
}
