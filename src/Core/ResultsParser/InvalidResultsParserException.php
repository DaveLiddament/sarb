<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\SarbException;

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
     * @param string $invalidOption
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
