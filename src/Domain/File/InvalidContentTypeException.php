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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * Used for when data supplied is not the correct content type.
 *
 * E.g. JSON was expected, but representation of data could not be converted to JSON.
 */
final class InvalidContentTypeException extends SarbException
{
    public static function notJson(): self
    {
        return new self('Not valid JSON');
    }
}
