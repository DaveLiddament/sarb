<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\internal\BaseLineResultsComparator;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\BaseLineResultsBuilder;
use PHPUnit\Framework\TestCase;

class BaseLineResultsComparatorTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    private const FILE_1 = 'one.txt';
    private const FILE_2 = 'two.txt';
    private const FILE_3 = 'three.txt';
    private const LINE_1 = 1;
    private const LINE_2 = 2;
    private const TYPE_1 = 'TYPE1';
    private const TYPE_2 = 'TYPE2';

    /**
     * @var BaseLineResultsComparator
     */
    private $baseLineResultsComparator;

    protected function setUp(): void
    {
        $baseLineResultsBuilder = new BaseLineResultsBuilder();
        $baseLineResultsBuilder->add(self::FILE_1, self::LINE_1, self::TYPE_1);
        $baseLineResultsBuilder->add(self::FILE_2, self::LINE_2, self::TYPE_2);
        $this->baseLineResultsComparator = new BaseLineResultsComparator($baseLineResultsBuilder->build());
    }

    public function dataProvider(): array
    {
        return [
            // [<expected result>, <file>, <line>, <type>]
            [
                false,
                self::FILE_3,
                self::LINE_1,
                self::TYPE_1,
            ],
            [
                true,
                self::FILE_1,
                self::LINE_1,
                self::TYPE_1,
            ],
            [
                true,
                self::FILE_1,
                self::LINE_1,
                self::TYPE_1,
            ],
            [
                false,
                self::FILE_2,
                self::LINE_1,
                self::TYPE_1,
            ],
            [
                false,
                self::FILE_1,
                self::LINE_2,
                self::TYPE_1,
            ],
            [
                false,
                self::FILE_2,
                self::LINE_2,
                self::TYPE_1,
            ],
            [
                false,
                self::FILE_1,
                self::LINE_1,
                self::TYPE_2,
            ],
            [
                false,
                self::FILE_2,
                self::LINE_1,
                self::TYPE_2,
            ],
            [
                false,
                self::FILE_1,
                self::LINE_2,
                self::TYPE_2,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testInBaseLine(bool $expected, string $fileName, int $lineNumber, string $type): void
    {
        $location = new Location(new FileName($fileName), new LineNumber($lineNumber));
        $actual = $this->baseLineResultsComparator->isInBaseLine($location, new Type($type));
        $this->assertSame($expected, $actual);
    }
}
