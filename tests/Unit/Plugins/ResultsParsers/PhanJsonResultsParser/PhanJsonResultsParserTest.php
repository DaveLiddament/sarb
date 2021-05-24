<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhanJsonResultsParser\PhanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\ExpectParseExceptionWithResultTrait;
use PHPUnit\Framework\TestCase;

class PhanJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
    use ExpectParseExceptionWithResultTrait;
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
        $this->projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phanJsonResultsParser = new PhanJsonResultsParser();
    }

    public function testConversionFromString(): void
    {
        $fileContents = $this->getResource('phan/phan.json');
        $analysisResults = $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);

        $this->assertCount(2, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];

        $this->assertMatch($result1,
            'src/Domain/Analyser/BaseLineResultsRemover.php',
            16,
            'PhanUnreferencedUseNormal'
        );
        $this->assertSame(
            'NOOPError PhanUnreferencedUseNormal Possibly zero references to use statement for classlike/namespace BaseLine (\DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine)',
            $result1->getMessage()
        );

        $this->assertMatch($result2,
            'src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php',
            107,
            'PhanPossiblyNullTypeArgument'
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phanJsonResultsParser->showTypeGuessingWarning());
    }

    /**
     * @psalm-return array<int,array{string, int}>
     */
    public function invalidFileProvider(): array
    {
        return [
            ['phan/phan-invalid-missing-check_name.json', 1],
            ['phan/phan-invalid-missing-description.json', 1],
            ['phan/phan-invalid-missing-file.json', 1],
            ['phan/phan-invalid-missing-line.json', 1],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName, int $resultWithIssue): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectParseAtLocationExceptionForResult($resultWithIssue);
        $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidContentTypeException::class);
        $this->phanJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
