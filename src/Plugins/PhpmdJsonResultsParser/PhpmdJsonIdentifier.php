<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpmdJsonIdentifier implements Identifier
{
    public function getCode(): string
    {
        return 'phpmd-json';
    }

    public function getDescription(): string
    {
        return 'PHP Mess Detector results (JSON format). To generate use: phpmd <files|directories to scan> json <rulesets> > <filename>.json';
    }
}
