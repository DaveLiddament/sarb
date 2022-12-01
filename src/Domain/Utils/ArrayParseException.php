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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

class ArrayParseException extends SarbException
{
    public static function missingKey(string $key): self
    {
        return new self("Missing key [$key]");
    }

    public static function invalidType(string $key, string $expectedType): self
    {
        $message = "Value for [$key] is not of the expected type [$expectedType]";

        return new self($message);
    }

    public static function invalidValue(string $key, string $value): self
    {
        $message = "Value [$value] for [$key] is not valid";

        return new self($message);
    }
}
