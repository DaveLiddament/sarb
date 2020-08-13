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

class SarbJsonIdentifier implements Identifier
{
    public const CODE = 'sarb-json';

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getDescription(): string
    {
        return 'SARB format';
    }
}
