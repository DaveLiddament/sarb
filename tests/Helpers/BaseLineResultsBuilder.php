<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;

class BaseLineResultsBuilder
{
    /**
     * @var array
     * @psalm-var array<mixed>
     */
    private $results;

    public function __construct()
    {
        $this->results = [];
    }

    public function add(string $fileName, int $lineNumber, string $type): self
    {
        $this->results[] = [
            'fileName' => $fileName,
            'lineNumber' => $lineNumber,
            'type' => $type,
            'message' => 'Message',
        ];

        return $this;
    }

    public function build(): BaseLineAnalysisResults
    {
        return BaseLineAnalysisResults::fromArray($this->results);
    }
}
