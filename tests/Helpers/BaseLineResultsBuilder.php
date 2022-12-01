<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;

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

    public function add(string $fileName, int $lineNumber, string $type, Severity $severity): self
    {
        $this->results[] = [
            'fileName' => $fileName,
            'lineNumber' => $lineNumber,
            'type' => $type,
            'message' => 'Message',
            'severity' => $severity->getSeverity(),
        ];

        return $this;
    }

    public function build(): BaseLineAnalysisResults
    {
        return BaseLineAnalysisResults::fromArray($this->results);
    }
}
