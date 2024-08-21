<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpMagicNumberDetectorResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

final class PhpMagicNumberDetectorIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'phpmnd';
    }

    public function getDescription(): string
    {
        return 'PHP Magic Number Detector';
    }

    public function getToolCommand(): string
    {
        return 'phpmnd <files|directories to scan>';
    }
}
