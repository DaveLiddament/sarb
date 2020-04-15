<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferJsonResultsParser\PhpCodeSnifferJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpCodeSnifferJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
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

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant', '/home');
        $this->phpCodeSnifferJsonResultsParser = new PhpCodeSnifferJsonResultsParser(new FqcnRemover());
        $this->fileContents = $this->getResource('phpCodeSniffer/full.json');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->phpCodeSnifferJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);

        $this->assertCount(6, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];
        $result3 = $analysisResults->getAnalysisResults()[2];
        $result4 = $analysisResults->getAnalysisResults()[3];
        $result5 = $analysisResults->getAnalysisResults()[4];
        $result6 = $analysisResults->getAnalysisResults()[5];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Domain/Common/InvalidPathException.php'),
                new LineNumber(2)
            ),
            new Type('Squiz.Commenting.FileComment.Missing')
        ));
        $this->assertSame(
            'Missing file doc comment',
            $result1->getMessage()
        );

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/Domain/Common/InvalidPathException.php'),
                new LineNumber(7)
            ),
            new Type('Squiz.Commenting.ClassComment.Missing')
        ));

        $this->assertTrue($result3->isMatch(
            new Location(
                new FileName('src/Domain/Common/InvalidPathException.php'),
                new LineNumber(9)
            ),
            new Type('Squiz.Commenting.FunctionComment.Missing')
        ));

        $this->assertTrue($result4->isMatch(
            new Location(
                new FileName('src/Domain/Common/InvalidPathException.php'),
                new LineNumber(11)
            ),
            new Type('Generic.Files.LineLength.TooLong')
        ));

        $this->assertTrue($result5->isMatch(
            new Location(
                new FileName('src/Domain/BaseLiner/BaseLineImporter.php'),
                new LineNumber(8)
            ),
            new Type('Generic.Files.LineLength.TooLong')
        ));

        $this->assertTrue($result6->isMatch(
            new Location(
                new FileName('src/Domain/BaseLiner/BaseLineImporter.php'),
                new LineNumber(52)
            ),
            new Type('Squiz.WhiteSpace.FunctionSpacing.Before')
        ));
    }

    public function testConvertToString(): void
    {
        $analysisResults = $this->phpCodeSnifferJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->phpCodeSnifferJsonResultsParser->convertToString($analysisResults);

        $this->assertJsonStringEqualsJsonString($this->fileContents, $asString);
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phpCodeSnifferJsonResultsParser->showTypeGuessingWarning());
    }
}
