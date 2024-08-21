<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Analyser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use PHPUnit\Framework\TestCase;

final class LocationSortTest extends TestCase
{
    public function testCompareTo(): void
    {
        $foo14 = $this->location('/foo', 14);
        $foo15 = $this->location('/foo', 15);
        $foo16 = $this->location('/foo', 16);
        $bar15 = $this->location('/bar', 15);
        $baz14 = $this->location('/baz', 14);

        $list = [
            $foo15,
            $foo14,
            $foo16,
            $bar15,
            $baz14,
        ];

        $expected = [
            $bar15,
            $baz14,
            $foo14,
            $foo15,
            $foo16,
        ];

        usort($list, function (Location $a, Location $b): int {
            return $a->compareTo($b);
        });

        $this->assertSame($expected, $list);
    }

    private function location(string $fileName, int $lineNumber): Location
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');

        return Location::fromAbsoluteFileName(
            new AbsoluteFileName($fileName),
            $projectRoot,
            new LineNumber($lineNumber),
        );
    }
}
