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

/**
 * Used for when file is in an invalid format (e.g. not a JSON file).
 */
class InvalidFileFormatException extends SarbException
{
}
