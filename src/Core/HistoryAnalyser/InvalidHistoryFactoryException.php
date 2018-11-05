<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\SarbException;

/**
 * Thrown if invalid name of HistoryAnalyser is supplied.
 */
class InvalidHistoryFactoryException extends SarbException
{
    public function __construct(string $name)
    {
        parent::__construct("Invalid HistoryAnalyser [$name]");
    }
}
