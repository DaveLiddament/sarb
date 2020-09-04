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

/**
 * Holds a parsing exception along with details of where the parsing failed.
 */
class ParseAtLocationException extends SarbException
{
    public static function issueAtPosition(SarbException $e, int $position): self
    {
        return new self("Issue with result [$position]. {$e->getMessage()}", 0, $e);
    }

    public static function issueParsing(SarbException $e, string $location): self
    {
        return new self("Issue parsing [$location]. {$e->getMessage()}", 0, $e);
    }

    public static function issueParsingWithMessage(string $message, string $location): self
    {
        return new self("Issue parsing [$location]. $message");
    }
}
