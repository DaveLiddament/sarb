<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Analyser\internal\BaseLineResultsComparator;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use PHPUnit\Framework\TestCase;

class BaseLineResultsComparatorTest extends TestCase
{
    use AnalysisResultsAdderTrait;

    private const FILE_1 = 'one.txt';
    private const FILE_2 = 'two.txt';
    private const LINE_1 = 1;
    private const LINE_2 = 2;
    private const TYPE_1 = 'TYPE1';
    private const TYPE_2 = 'TYPE2';

    /**
     * @var BaseLineResultsComparator
     */
    private $baseLineResultsComparator;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $analysisResults = new AnalysisResults();
        $this->addAnalysisResult($analysisResults, self::FILE_1, self::LINE_1, self::TYPE_1);
        $this->addAnalysisResult($analysisResults, self::FILE_2, self::LINE_2, self::TYPE_2);
        $this->baseLineResultsComparator = new BaseLineResultsComparator($analysisResults);
    }

    public function dataProvider(): array
    {
        return [
            // [<expected result>, <file>, <line>, <type>]
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
     *
     * @param bool $expected
     * @param string $fileName
     * @param int $lineNumber
     * @param string $type
     */
    public function testInBaseLine(bool $expected, string $fileName, int $lineNumber, string $type): void
    {
        $location = new Location(new FileName($fileName), new LineNumber($lineNumber));
        $actual = $this->baseLineResultsComparator->isInBaseLine($location, new Type($type));
        $this->assertSame($expected, $actual);
    }
}
