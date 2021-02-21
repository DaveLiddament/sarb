<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class ResultsParserStubIdentifier implements Identifier
{
    public const CODE = 'results-parser-stub';

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getDescription(): string
    {
        return 'description of '.self::CODE;
    }

    public function getToolCommand(): string
    {
        return 'tool';
    }
}
