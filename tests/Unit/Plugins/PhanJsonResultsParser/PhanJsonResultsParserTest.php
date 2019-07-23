<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
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

    /**
     * @var string
     */
    private $fileContents;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $this->phanJsonResultsParser = new PhanJsonResultsParser();
        $this->fileContents = $this->getResource('phan/phan.json');
    }

    public function testConversionFromString(): void
    {
        $analysisResults = $this->phanJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);

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

    public function testConvertToString(): void
    {
        $analysisResults = $this->phanJsonResultsParser->convertFromString($this->fileContents, $this->projectRoot);
        $asString = $this->phanJsonResultsParser->convertToString($analysisResults);

        $this->assertFileContentsSame($this->fileContents, $asString);
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phanJsonResultsParser->showTypeGuessingWarning());
    }
}
