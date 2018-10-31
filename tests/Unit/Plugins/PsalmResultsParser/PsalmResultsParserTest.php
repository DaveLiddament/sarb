<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Unit\Plugins\PsalmResultsParser;

use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Location;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\Type;
use DaveLiddament\StaticAnalysisBaseliner\Plugins\PsalmResultsParser\PsalmJsonResultsParser;
use DaveLiddament\StaticAnalysisBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PsalmResultsParserTest extends TestCase
{
    use ResourceLoaderTrait;

    public function testConversion(): void
    {
        $psalmResultsParser = new PsalmJsonResultsParser();
        $original = $this->getResource('psalm/psalm.json');

        // Convert both ways
        $analysisResults = $psalmResultsParser->convertFromString($original);

        $this->assertCount(2, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/DiffHistoryAnalyser/internal/OriginalLineNumberCalculator.php'),
                new LineNumber(25)
            ),
            new Type('PossiblyNullReference')
        ));

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/DiffHistoryAnalyser/internal/OriginalLineNumberCalculator.php'),
                new LineNumber(41)
            ),
            new Type('PossiblyNullReference')
        ));
    }
}
