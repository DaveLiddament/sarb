<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class SarbRelativeFileJsonIdentifier implements Identifier
{
    public const CODE = 'sarb-relative-json';

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getDescription(): string
    {
        return 'SARB format for outputs with relative paths';
    }

    public function getToolCommand(): string
    {
        return 'sarb-relative-path-outputting-tool';
    }
}
