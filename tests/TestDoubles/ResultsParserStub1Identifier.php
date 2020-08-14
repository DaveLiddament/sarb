<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class ResultsParserStub1Identifier implements Identifier
{
    const CODE = 'stub_1';

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'description of '.self::CODE;
    }
}
