<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\ExakatJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

final class ExakatJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'exakat-sarb';
    }

    public function getDescription(): string
    {
        return 'Exakat results (SARB support).';
    }

    public function getToolCommand(): string
    {
        return 'php exakat.phar report -p <project> -format Sarb';
    }
}
