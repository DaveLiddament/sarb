<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PsalmResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser\PsalmJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PsalmResultsParserTest extends TestCase
{
    use ResourceLoaderTrait;

    public function testConversion(): void
    {
        $projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $psalmResultsParser = new PsalmJsonResultsParser();
        $original = $this->getResource('psalm/psalm.json');

        // Convert both ways
        $analysisResults = $psalmResultsParser->convertFromString($original, $projectRoot);

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
