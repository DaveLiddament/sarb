<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

final class PhpCodeSnifferJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'phpcodesniffer-json';
    }

    public function getDescription(): string
    {
        return 'PHP Code Sniffer (JSON format).';
    }

    public function getToolCommand(): string
    {
        return 'phpcs --report=json <code directory>';
    }
}
