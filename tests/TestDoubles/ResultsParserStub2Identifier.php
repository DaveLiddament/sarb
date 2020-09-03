<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class ResultsParserStub2Identifier implements Identifier
{
    const CODE = 'stub_2';

    public function getCode(): string
    {
        return self::CODE;
    }

    public function getDescription(): string
    {
        return 'description of '.self::CODE;
    }
}
