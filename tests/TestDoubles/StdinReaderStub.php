<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles;

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\StdinReader;

class StdinReaderStub implements StdinReader
{
    /**
     * @var string
     */
    private $stdin;

    public function __construct(string $stdin)
    {
        $this->stdin = $stdin;
    }

    public function getStdin(): string
    {
        return $this->stdin;
    }
}
