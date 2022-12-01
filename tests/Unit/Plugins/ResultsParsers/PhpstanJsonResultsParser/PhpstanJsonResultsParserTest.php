<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpstanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser\PhpstanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpstanJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
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

    protected function setUp(): void
    {
        $this->projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');
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

        $this->assertMatch($result1,
            'src/Domain/BaseLiner/BaseLineImporter.php',
            89,
            'Parameter #1 $array of static method expects int, array given.',
            Severity::error()
        );
        $this->assertSame(
            'Parameter #1 $array of static method DaveLiddament\\StaticAnalysisResultsBaseliner\\Domain\\ResultsParser\\AnalysisResults::fromArray() expects int, array given.',
            $result1->getMessage()
        );

        $this->assertMatch($result2,
            'src/Domain/ResultsParser/AnalysisResults.php',
            0,
            'Argument of an invalid type int supplied for foreach, only iterables are supported.',
            Severity::error()
        );

        $this->assertMatch($result3,
            'src/Domain/ResultsParser/AnalysisResults.php',
            73,
            'PHPDoc tag @param for parameter $array with type array is incompatible with native type int',
            Severity::error()
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertTrue($this->phpstanJsonResultsParser->showTypeGuessingWarning());
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidContentTypeException::class);
        $this->phpstanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    /**
     * @psalm-return array<int,array{string}>
     */
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
