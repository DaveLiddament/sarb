<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser\PsalmJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PsalmJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var PsalmJsonResultsParser
     */
    private $psalmResultsParser;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $this->psalmResultsParser = new PsalmJsonResultsParser();
        $original = $this->getResource('psalm/psalm.json');

        // Convert both ways
        $this->analysisResults = $this->psalmResultsParser->convertFromString($original, $projectRoot);
    }

    public function testConversion(): void
    {
        $this->assertCount(3, $this->analysisResults->getAnalysisResults());

        $result1 = $this->analysisResults->getAnalysisResults()[0];
        $result2 = $this->analysisResults->getAnalysisResults()[1];
        $result3 = $this->analysisResults->getAnalysisResults()[2];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Domain/ResultsParser/AnalysisResults.php'),
                new LineNumber(67)
            ),
            new Type('MismatchingDocblockParamType')
        ));
        $this->assertSame(
            "Parameter \$array has wrong type 'array<mixed, mixed>', should be 'int'",
            $result1->getMessage()
        );

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/Domain/Utils/JsonUtils.php'),
                new LineNumber(29)
            ),
            new Type('MixedAssignment')
        ));

        $this->assertTrue($result3->isMatch(
            new Location(
                new FileName('src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php'),
                new LineNumber(90)
            ),
            new Type('MixedAssignment')
        ));
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->psalmResultsParser->showTypeGuessingWarning());
    }
}
