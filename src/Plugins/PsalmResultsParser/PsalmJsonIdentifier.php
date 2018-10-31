<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Plugins\PsalmResultsParser;

use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\Identifier;

class PsalmJsonIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'psalm-json';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Psalm results (JSON format)';
    }
}
