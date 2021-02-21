<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhanJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'phan-json';
    }

    public function getDescription(): string
    {
        return 'Phan results (JSON format)';
    }

    public function getToolCommand(): string
    {
        return 'phan -m json';
    }
}
