<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\UnifiedDiffParser\DiffParserTestSupport;

class ExpectedDiff
{
    /**
     * @var string
     */
    private $diffFileName;

    /**
     * @var ExpectedFileMutations[]
     */
    private $expectedFileMutations;

    public static function fileName(string $diffFileName): self
    {
        return new self($diffFileName);
    }

    /**
     * ExpectedDiff constructor.
     */
    private function __construct(string $diffFileName)
    {
        $this->diffFileName = $diffFileName;
        $this->expectedFileMutations = [];
    }

    public function addExpectedFileMutations(ExpectedFileMutations $expectedFileMutations): self
    {
        $this->expectedFileMutations[] = $expectedFileMutations;

        return $this;
    }

    public function getDiffFileName(): string
    {
        return $this->diffFileName;
    }

    /**
     * @return ExpectedFileMutations[]
     */
    public function getExpectedFileMutations(): array
    {
        return $this->expectedFileMutations;
    }
}
