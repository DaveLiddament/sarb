<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PsalmJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'psalm-json';
    }

    public function getDescription(): string
    {
        return 'Psalm results (JSON format).';
    }

    public function getToolCommand(): string
    {
        return 'psalm --output-format=json';
    }
}
