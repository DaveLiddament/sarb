<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PsalmTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmTextResultsParser\PsalmTextResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PsalmTextResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PsalmTextResultsParser
     */
    private $psalmTextResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->psalmTextResultsParser = new PsalmTextResultsParser();
        $this->fileContents = $this->getResource('psalm/psalm.txt');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->psalmTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $this->assertCount(3, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];
        $result3 = $analysisResults->getAnalysisResults()[2];

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

    /**
     * @deprecated https://trello.com/c/Lj8VCsbY
     */
    public function testConvertToString(): void
    {
        $analysisResults = $this->psalmTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->psalmTextResultsParser->convertToString($analysisResults);

        $this->assertFileContentsSame($this->getResource('psalm/psalm-only-errors.txt'), $asString);
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->psalmTextResultsParser->showTypeGuessingWarning());
    }
}
