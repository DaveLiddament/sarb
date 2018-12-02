<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PsalmTextIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'psalm-text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Psalm results (text format). To generate use: psalm --report=<filename>.txt';
    }
}
