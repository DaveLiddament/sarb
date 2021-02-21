<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpstanJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'phpstan-json';
    }

    public function getDescription(): string
    {
        return 'PHPStan results (JSON format).';
    }

    public function getToolCommand(): string
    {
        return 'phpstan analyse --format=json';
    }
}
