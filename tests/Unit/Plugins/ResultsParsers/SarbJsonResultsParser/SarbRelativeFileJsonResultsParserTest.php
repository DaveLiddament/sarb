<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\SarbJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbRelativeFileJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\ExpectParseExceptionWithResultTrait;
use PHPUnit\Framework\TestCase;

class SarbRelativeFileJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
    use ExpectParseExceptionWithResultTrait;
    use ResourceLoaderTrait;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var SarbRelativeFileJsonResultsParser
     */
    private $sarbRelativeFileJsonResultsParser;
    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $this->sarbRelativeFileJsonResultsParser = new SarbRelativeFileJsonResultsParser();
    }

    public function testConversion(): void
    {
        $fileContents = $this->getResource('sarb-relative/sarb.json');
        $this->analysisResults = $this->sarbRelativeFileJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
        $this->assertCount(3, $this->analysisResults->getAnalysisResults());

        $result1 = $this->analysisResults->getAnalysisResults()[0];
        $result2 = $this->analysisResults->getAnalysisResults()[1];
        $result3 = $this->analysisResults->getAnalysisResults()[2];

        $this->assertMatch($result1,
            'src/Domain/ResultsParser/AnalysisResults.php',
            67,
            'MismatchingDocblockParamType',
            Severity::error()
        );
        $this->assertSame(
            "Parameter \$array has wrong type 'array<mixed, mixed>', should be 'int'",
            $result1->getMessage()
        );
        $this->assertSame(
            '/vagrant/static-analysis-baseliner/src/Domain/ResultsParser/AnalysisResults.php',
            $result1->getLocation()->getAbsoluteFileName()->getFileName()
        );

        $this->assertMatch($result2,
            'src/Domain/Utils/JsonUtils.php',
            29,
            'MixedAssignment',
            Severity::error()
        );

        $this->assertMatch($result3,
            'src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php',
            90,
            'MixedAssignment',
            Severity::warning()
        );
    }

    public function testConversionWithRelativePath(): void
    {
        $fileContents = $this->getResource('sarb-relative/sarb.json');
        $projectRoot = $this->projectRoot->withRelativePath('code');
        $this->analysisResults = $this->sarbRelativeFileJsonResultsParser->convertFromString($fileContents, $projectRoot);
        $this->assertCount(3, $this->analysisResults->getAnalysisResults());

        $result1 = $this->analysisResults->getAnalysisResults()[0];
        $result2 = $this->analysisResults->getAnalysisResults()[1];
        $result3 = $this->analysisResults->getAnalysisResults()[2];

        $this->assertMatch($result1,
            'src/Domain/ResultsParser/AnalysisResults.php',
            67,
            'MismatchingDocblockParamType',
            Severity::error()
        );
        $this->assertSame(
            "Parameter \$array has wrong type 'array<mixed, mixed>', should be 'int'",
            $result1->getMessage()
        );
        $this->assertSame(
            '/vagrant/static-analysis-baseliner/code/src/Domain/ResultsParser/AnalysisResults.php',
            $result1->getLocation()->getAbsoluteFileName()->getFileName()
        );

        $this->assertMatch($result2,
            'src/Domain/Utils/JsonUtils.php',
            29,
            'MixedAssignment',
            Severity::error()
        );

        $this->assertMatch($result3,
            'src/Plugins/PsalmJsonResultsParser/PsalmJsonResultsParser.php',
            90,
            'MixedAssignment',
            Severity::warning()
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->sarbRelativeFileJsonResultsParser->showTypeGuessingWarning());
    }

    /**
     * @psalm-return array<int,array{string, int}>
     */
    public function invalidFileProvider(): array
    {
        return [
            ['sarb-relative/sarb-invalid-missing-description.json', 1],
            ['sarb-relative/sarb-invalid-missing-file.json', 2],
            ['sarb-relative/sarb-invalid-missing-line.json', 2],
            ['sarb-relative/sarb-invalid-missing-type.json', 3],
            ['sarb-relative/sarb-invalid-severity.json', 2],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName, int $resultWithIssue): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectParseAtLocationExceptionForResult($resultWithIssue);
        $this->sarbRelativeFileJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidContentTypeException::class);
        $this->sarbRelativeFileJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
