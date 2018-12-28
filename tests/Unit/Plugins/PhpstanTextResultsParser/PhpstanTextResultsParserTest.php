<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpstanTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanTextResultsParser\PhpstanTextResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpstanTextResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var PhpstanTextResultsParser
     */
    private $phpstanTextResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phpstanTextResultsParser = new PhpstanTextResultsParser(new FqcnRemover());
        $this->fileContents = $this->getResource('phpstan/phpstan.txt');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->phpstanTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);

        $this->assertCount(1, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Plugins/PsalmTextResultsParser/PsalmTextResultsParser.php'),
                new LineNumber(50)
            ),
            new Type('Call to an undefined method')
        ));
    }

    public function testConvertToString(): void
    {
        $analysisResults = $this->phpstanTextResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->phpstanTextResultsParser->convertToString($analysisResults);

        $this->assertFileContentsSame($this->fileContents, $asString);
    }

    public function testTypeGuesser(): void
    {
        $this->assertTrue($this->phpstanTextResultsParser->showTypeGuessingWarning());
    }
}
