<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * Thrown if invalid name of HistoryAnalyser is supplied.
 */
final class InvalidHistoryFactoryException extends SarbException
{
    public static function invalidIdentifier(string $name): self
    {
        return new self("Invalid identifier [$name]");
    }
}
