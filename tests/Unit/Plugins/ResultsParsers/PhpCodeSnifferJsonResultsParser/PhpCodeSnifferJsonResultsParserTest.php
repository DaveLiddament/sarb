<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser\PhpCodeSnifferJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpCodeSnifferJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PhpCodeSnifferJsonResultsParser
     */
    private $phpCodeSnifferJsonResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp(): void
    {
        $this->projectRoot = new ProjectRoot('/vagrant', '/home');
        $this->phpCodeSnifferJsonResultsParser = new PhpCodeSnifferJsonResultsParser();
        $this->fileContents = $this->getResource('phpCodeSniffer/full.json');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->phpCodeSnifferJsonResultsParser->convertFromString($this->fileContents,
            $this->projectRoot);

        $this->assertCount(6, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];
        $result3 = $analysisResults->getAnalysisResults()[2];
        $result4 = $analysisResults->getAnalysisResults()[3];
        $result5 = $analysisResults->getAnalysisResults()[4];
        $result6 = $analysisResults->getAnalysisResults()[5];

        $this->assertMatch($result1,
            'src/Domain/BaseLiner/BaseLineImporter.php',
            8,
            'Generic.Files.LineLength.TooLong'
        );

        $this->assertMatch($result2,
            'src/Domain/BaseLiner/BaseLineImporter.php',
            52,
            'Squiz.WhiteSpace.FunctionSpacing.Before'
        );

        $this->assertMatch($result3,
            'src/Domain/Common/InvalidPathException.php',
            2,
            'Squiz.Commenting.FileComment.Missing'
        );
        $this->assertSame(
            'Missing file doc comment',
            $result3->getMessage()
        );

        $this->assertMatch($result4,
            'src/Domain/Common/InvalidPathException.php',
            7,
            'Squiz.Commenting.ClassComment.Missing'
        );

        $this->assertMatch($result5,
            'src/Domain/Common/InvalidPathException.php',
            9,
            'Squiz.Commenting.FunctionComment.Missing'
        );

        $this->assertMatch($result6,
            'src/Domain/Common/InvalidPathException.php',
            11,
            'Generic.Files.LineLength.TooLong'
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phpCodeSnifferJsonResultsParser->showTypeGuessingWarning());
    }

    public function testInvalidJsonInput(): void
    {
        $fileContents = $this->getResource('invalid-json.json');
        $this->expectException(InvalidFileFormatException::class);
        $this->phpCodeSnifferJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }

    /**
     * @phpstan-return array<mixed>
     */
    public function invalidFileProvider(): array
    {
        return [
            ['phpCodeSniffer/invalid-filename.json'],
            ['phpCodeSniffer/invalid-missing-files.json'],
            ['phpCodeSniffer/invalid-missing-line.json'],
            ['phpCodeSniffer/invalid-missing-message.json'],
            ['phpCodeSniffer/invalid-missing-type.json'],
        ];
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $fileContents = $this->getResource($fileName);
        $this->expectException(ParseAtLocationException::class);
        $this->phpCodeSnifferJsonResultsParser->convertFromString($fileContents, $this->projectRoot);
    }
}
