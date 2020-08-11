<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpstanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser\PhpstanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpstanJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PhpstanJsonResultsParser
     */
    private $phpstanJsonResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phpstanJsonResultsParser = new PhpstanJsonResultsParser(new FqcnRemover());
        $this->fileContents = $this->getResource('phpstan/phpstan.json');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->phpstanJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);

        $this->assertCount(3, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];
        $result3 = $analysisResults->getAnalysisResults()[2];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Domain/BaseLiner/BaseLineImporter.php'),
                new LineNumber(89)
            ),
            new Type('Parameter #1 $array of static method expects int, array given.')
        ));
        $this->assertSame(
            'Parameter #1 $array of static method DaveLiddament\\StaticAnalysisResultsBaseliner\\Domain\\ResultsParser\\AnalysisResults::fromArray() expects int, array given.',
            $result1->getMessage()
        );

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/Domain/ResultsParser/AnalysisResults.php'),
                new LineNumber(73)
            ),
            new Type('PHPDoc tag @param for parameter $array with type array is incompatible with native type int')
        ));

        $this->assertTrue($result3->isMatch(
            new Location(
                new FileName('src/Domain/ResultsParser/AnalysisResults.php'),
                new LineNumber(0)
            ),
            new Type('Argument of an invalid type int supplied for foreach, only iterables are supported.')
        ));
    }

    public function testTypeGuesser(): void
    {
        $this->assertTrue($this->phpstanJsonResultsParser->showTypeGuessingWarning());
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidFileFormatException::class);
        $this->phpstanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    public function invalidFileProvider(): array
    {
        return [
            ['phpstan/phpstan-invalid-missing-description.json'],
            ['phpstan/phpstan-invalid-missing-file.json'],
            ['phpstan/phpstan-invalid-missing-files.json'],
            ['phpstan/phpstan-invalid-missing-line.json'],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectException(ParseAtLocationException::class);
        $this->phpstanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
