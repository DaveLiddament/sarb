<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhanJsonResultsParser\PhanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhanJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PhanJsonResultsParser
     */
    private $phanJsonResultsParser;

    protected function setUp(): void
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phanJsonResultsParser = new PhanJsonResultsParser();
    }

    public function testConversionFromString(): void
    {
        $fileContents = $this->getResource('phan/phan.json');
        $analysisResults = $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);

        $this->assertCount(2, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Domain/Analyser/BaseLineResultsRemover.php'),
                new LineNumber(16)
            ),
            new Type('PhanUnreferencedUseNormal')
        ));
        $this->assertSame(
            'NOOPError PhanUnreferencedUseNormal Possibly zero references to use statement for classlike/namespace BaseLine (\DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine)',
            $result1->getMessage()
        );

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php'),
                new LineNumber(107)
            ),
            new Type('PhanPossiblyNullTypeArgument')
        ));
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phanJsonResultsParser->showTypeGuessingWarning());
    }

    public function invalidFileProvider(): array
    {
        return [
            ['phan/phan-invalid-missing-check_name.json'],
            ['phan/phan-invalid-missing-description.json'],
            ['phan/phan-invalid-missing-file.json'],
            ['phan/phan-invalid-missing-line.json'],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectException(ParseAtLocationException::class);
        $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidFileFormatException::class);
        $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
