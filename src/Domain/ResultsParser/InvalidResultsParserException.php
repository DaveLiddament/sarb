<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * Thrown if invalid ResultsParser is given.
 */
class InvalidResultsParserException extends SarbException
{
    /**
     * @var Identifier[]
     */
    private $possibleOptions;

    /**
     * InvalidResultsParserException constructor.
     *
     * @param Identifier[] $possibleValues
     */
    public function __construct(string $invalidOption, array $possibleValues)
    {
        parent::__construct("Invalid {$invalidOption}");
        $this->possibleOptions = $possibleValues;
    }

    /**
     * @return Identifier[]
     */
    public function getPossibleOptions(): array
    {
        return $this->possibleOptions;
    }
}
