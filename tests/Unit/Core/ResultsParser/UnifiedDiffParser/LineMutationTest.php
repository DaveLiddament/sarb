<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use PHPUnit\Framework\TestCase;

class LineMutationTest extends TestCase
{
    private const LINE_NUMBER_1 = 1;
    private const LINE_NUMBER_2 = 2;

    public function testNewLineNumberSet(): void
    {
        $lineMutation = LineMutation::newLineNumber($this->getLineNumber1());
        $this->assertNull($lineMutation->getOriginalLine());
        $this->assertNotNull($lineMutation->getNewLine());
        /** @psalm-suppress PossiblyNullArgument */
        $this->assertLineNumber($this->getLineNumber1(), $lineMutation->getNewLine());
    }

    public function testOriginalLineNumberSet(): void
    {
        $lineMutation = LineMutation::originalLineNumber($this->getLineNumber1());
        $this->assertNull($lineMutation->getNewLine());
        $this->assertNotNull($lineMutation->getOriginalLine());
        /** @psalm-suppress PossiblyNullArgument */
        $this->assertLineNumber($this->getLineNumber1(), $lineMutation->getOriginalLine());
    }

    /**
     * @psalm-return array<string,array{LineMutation,LineMutation|null}>
     */
    public function notEqualDataProvider(): array
    {
        return [
            'compareWithNull' => [
                LineMutation::originalLineNumber($this->getLineNumber1()),
                null,
            ],
            'bothOriginalLineNumberDifferentLineNumbers' => [
                LineMutation::originalLineNumber($this->getLineNumber1()),
                LineMutation::originalLineNumber($this->getLineNumber2()),
            ],
            'bothNewLineNumberDifferentLineNumbers' => [
                LineMutation::newLineNumber($this->getLineNumber1()),
                LineMutation::newLineNumber($this->getLineNumber2()),
            ],
            'bothSameLineNumberForOriginalAndNew' => [
                LineMutation::newLineNumber($this->getLineNumber1()),
                LineMutation::originalLineNumber($this->getLineNumber2()),
            ],
        ];
    }

    /**
     * @dataProvider notEqualDataProvider
     */
    public function testNotEqual(LineMutation $a, ?LineMutation $b): void
    {
        $this->assertFalse($a->isEqual($b));
    }

    /**
     * @psalm-return array<string,array{LineMutation,LineMutation}>
     */
    public function equalDataProvider(): array
    {
        return [
            'sameOriginalLineNumber' => [
                LineMutation::originalLineNumber($this->getLineNumber1()),
                LineMutation::originalLineNumber($this->getLineNumber1()),
            ],
            'sameNewLineNumber' => [
                LineMutation::newLineNumber($this->getLineNumber1()),
                LineMutation::newLineNumber($this->getLineNumber1()),
            ],
        ];
    }

    /**
     * @dataProvider equalDataProvider
     */
    public function testEqual(LineMutation $a, LineMutation $b): void
    {
        $this->assertTrue($a->isEqual($b));
    }

    private function getLineNumber1(): LineNumber
    {
        return new LineNumber(self::LINE_NUMBER_1);
    }

    private function getLineNumber2(): LineNumber
    {
        return new LineNumber(self::LINE_NUMBER_2);
    }

    private function assertLineNumber(LineNumber $expected, LineNumber $actual): void
    {
        $this->assertSame($expected->getLineNumber(), $actual->getLineNumber());
    }
}
