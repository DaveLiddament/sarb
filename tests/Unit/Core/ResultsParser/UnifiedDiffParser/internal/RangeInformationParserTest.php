<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\DiffParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\RangeInformation;
use PHPUnit\Framework\TestCase;

class RangeInformationParserTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['@@ -3,5 +3,5 @@', 3, 5, 3, 5],
            ['@@ -1,7 +1,5 @@ function foo', 1, 7, 1, 5],
            ['@@ -10,5 +8,3 @@ function foo', 10, 5, 8, 3],
            ['@@ -10,395 +8,200 @@ function foo', 10, 395, 8, 200],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $rangeInformationAsString
     * @param int $originalStartLine
     * @param int $originalHunkSize
     * @param int $newStartLine
     * @param int $newHunkSize
     *
     * @throws DiffParseException
     */
    public function testHappyPath(
        string $rangeInformationAsString,
        int $originalStartLine,
        int $originalHunkSize,
        int $newStartLine,
        int $newHunkSize
    ): void {
        $rangeInformation = new RangeInformation($rangeInformationAsString);
        $this->assertEquals($originalStartLine, $rangeInformation->getOriginalFileStartLine());
        $this->assertEquals($originalHunkSize, $rangeInformation->getOriginalFileHunkSize());
        $this->assertEquals($newStartLine, $rangeInformation->getNewFileStartLine());
        $this->assertEquals($newHunkSize, $rangeInformation->getNewFileHunkSize());
    }

    public function invalidDataProvider(): array
    {
        return [
            ['@@ 3,5 10,2 @@'],
            ['@@ -3,5 9,4 @@'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @param string $rangeInformationString
     */
    public function testInvalidData(string $rangeInformationString): void
    {
        try {
            new RangeInformation($rangeInformationString);
            $this->fail('No exception thrown');
        } catch (DiffParseException $e) {
            $this->assertEquals($rangeInformationString, $e->getDetails());
            $this->assertEquals('RANGE_INFORMATION_PARSE_ERROR', $e->getReason());
        }
    }
}
