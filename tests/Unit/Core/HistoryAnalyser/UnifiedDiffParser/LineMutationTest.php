<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\HistoryAnalyser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\LineMutation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LineMutationTest extends TestCase
{
    private const LINE_NUMBER_1 = 1;
    private const LINE_NUMBER_2 = 2;

    public function testNewLineNumberSet(): void
    {
        $lineMutation = LineMutation::newLineNumber(self::getLineNumber1());
        $this->assertNull($lineMutation->getOriginalLine());
        $this->assertNotNull($lineMutation->getNewLine());
        /** @psalm-suppress PossiblyNullArgument */
        $this->assertLineNumber(self::getLineNumber1(), $lineMutation->getNewLine());
    }

    public function testOriginalLineNumberSet(): void
    {
        $lineMutation = LineMutation::originalLineNumber(self::getLineNumber1());
        $this->assertNull($lineMutation->getNewLine());
        $this->assertNotNull($lineMutation->getOriginalLine());
        /** @psalm-suppress PossiblyNullArgument */
        $this->assertLineNumber(self::getLineNumber1(), $lineMutation->getOriginalLine());
    }

    /**
     * @return array<string,array{LineMutation,LineMutation|null}>
     */
    public static function notEqualDataProvider(): array
    {
        return [
            'compareWithNull' => [
                LineMutation::originalLineNumber(self::getLineNumber1()),
                null,
            ],
            'bothOriginalLineNumberDifferentLineNumbers' => [
                LineMutation::originalLineNumber(self::getLineNumber1()),
                LineMutation::originalLineNumber(self::getLineNumber2()),
            ],
            'bothNewLineNumberDifferentLineNumbers' => [
                LineMutation::newLineNumber(self::getLineNumber1()),
                LineMutation::newLineNumber(self::getLineNumber2()),
            ],
            'bothSameLineNumberForOriginalAndNew' => [
                LineMutation::newLineNumber(self::getLineNumber1()),
                LineMutation::originalLineNumber(self::getLineNumber2()),
            ],
        ];
    }

    #[DataProvider('notEqualDataProvider')]
    public function testNotEqual(LineMutation $a, ?LineMutation $b): void
    {
        $this->assertFalse($a->isEqual($b));
    }

    /**
     * @return array<string,array{LineMutation,LineMutation}>
     */
    public static function equalDataProvider(): array
    {
        return [
            'sameOriginalLineNumber' => [
                LineMutation::originalLineNumber(self::getLineNumber1()),
                LineMutation::originalLineNumber(self::getLineNumber1()),
            ],
            'sameNewLineNumber' => [
                LineMutation::newLineNumber(self::getLineNumber1()),
                LineMutation::newLineNumber(self::getLineNumber1()),
            ],
        ];
    }

    #[DataProvider('equalDataProvider')]
    public function testEqual(LineMutation $a, LineMutation $b): void
    {
        $this->assertTrue($a->isEqual($b));
    }

    private static function getLineNumber1(): LineNumber
    {
        return new LineNumber(self::LINE_NUMBER_1);
    }

    private static function getLineNumber2(): LineNumber
    {
        return new LineNumber(self::LINE_NUMBER_2);
    }

    private function assertLineNumber(LineNumber $expected, LineNumber $actual): void
    {
        $this->assertSame($expected->getLineNumber(), $actual->getLineNumber());
    }
}
