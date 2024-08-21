<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * Thrown if invalid ResultsParser is given.
 */
final class InvalidResultsParserException extends SarbException
{
    public static function invalidIdentifier(string $identifier): self
    {
        return new self("Invalid ResultsParser [$identifier]");
    }
}
