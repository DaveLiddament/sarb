<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ExakatJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ExakatJsonResultsParser\ExakatJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class ExakatJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var ExakatJsonResultsParser
     */
    private $exakatJsonResultsParser;
    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $this->exakatJsonResultsParser = new ExakatJsonResultsParser();
    }

    public function testConversion(): void
    {
        $fileContents = $this->getResource('exakat/exakat.json');
        $this->analysisResults = $this->exakatJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
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
        $this->assertSame('', $result1->getMessage());

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
        $this->assertFalse($this->exakatJsonResultsParser->showTypeGuessingWarning());
    }

    public function invalidFileProvider(): array
    {
        return [
            ['exakat/exakat-invalid-missing-type.json'],
            ['exakat/exakat-invalid-missing-file.json'],
            ['exakat/exakat-invalid-missing-line.json'],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectException(ParseAtLocationException::class);
        $this->exakatJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidFileFormatException::class);
        $this->exakatJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
