<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpstanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanJsonResultsParser\PhpstanJsonResultsParser;
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
    private $PhpstanJsonResultsParser;

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->PhpstanJsonResultsParser = new PhpstanJsonResultsParser(new FqcnRemover());
        $this->fileContents = $this->getResource('phpstan/phpstan.json');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->PhpstanJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);

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
                new LineNumber(78)
            ),
            new Type('Argument of an invalid type int supplied for foreach, only iterables are supported.')
        ));
    }

    public function testConvertToString(): void
    {
        $analysisResults = $this->PhpstanJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->PhpstanJsonResultsParser->convertToString($analysisResults);

        $this->assertFileContentsSame($this->fileContents, $asString);
    }
}
