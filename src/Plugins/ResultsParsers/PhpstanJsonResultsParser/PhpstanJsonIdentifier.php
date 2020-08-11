<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PhpstanJsonIdentifier implements Identifier
{
    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return 'phpstan-json-tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'PHPStan results (JSON format). To generate use: phpstan analyse --error-format json > <filename>.json  NOTE: this will be deprecated once this enhancement has been released: https://github.com/phpstan/phpstan/issues/1686';
    }
}
