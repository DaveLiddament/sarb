<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\ExakatJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\ExakatJsonResultsParser\ExakatJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class ExakatJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
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

    protected function setUp(): void
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

        $this->assertMatch($result1,
           'src/Domain/ResultsParser/AnalysisResults.php',
           67,
           'MismatchingDocblockParamType'
        );
        $this->assertSame('', $result1->getMessage());

        $this->assertMatch($result2,
            'src/Domain/Utils/JsonUtils.php',
            29,
             'MixedAssignment'
        );

        $this->assertMatch($result3,
            'src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php',
            90,
            'MixedAssignment'
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->exakatJsonResultsParser->showTypeGuessingWarning());
    }

    /**
     * @psalm-return array<int,array{string}>
     */
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
