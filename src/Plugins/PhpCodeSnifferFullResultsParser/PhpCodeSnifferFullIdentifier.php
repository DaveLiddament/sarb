<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpCodeSnifferFullIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'phpcodesniffer-full';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'PHP Code Sniffer (default format). To generate use: phpcs <code directory> > <output.txt> ';
    }
}
