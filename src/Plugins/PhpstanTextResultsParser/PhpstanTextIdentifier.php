<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpstanTextIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'phpstan-text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'PHPStan results (text format). To generate use: phpstan analyse --error-format raw > <filename>.txt';
    }
}
