<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpmdJsonResultsParser\PhpmdJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpmdJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use ResourceLoaderTrait;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var PhpmdJsonResultsParser
     */
    private $phpmdResultsParser;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $this->phpmdResultsParser = new PhpmdJsonResultsParser();
        $original = $this->getResource('phpmd/phpmd.json');

        // Convert both ways
        $this->analysisResults = $this->phpmdResultsParser->convertFromString($original, $projectRoot);
    }

    public function testConversion(): void
    {
        $this->assertCount(3, $this->analysisResults->getAnalysisResults());

        $result1 = $this->analysisResults->getAnalysisResults()[0];
        $result2 = $this->analysisResults->getAnalysisResults()[1];
        $result3 = $this->analysisResults->getAnalysisResults()[2];

        $this->assertTrue($result1->isMatch(
            new Location(
                new FileName('src/Domain/Analyser/BaseLineResultsRemover.php'),
                new LineNumber(28)
            ),
            new Type('LongVariable')
        ));
        $this->assertSame(
            'Avoid excessively long variable names like $latestAnalysisResults. Keep variable name length under 20.',
            $result1->getMessage()
        );

        $this->assertTrue($result2->isMatch(
            new Location(
                new FileName('src/Domain/Analyser/BaseLineResultsRemover.php'),
                new LineNumber(30)
            ),
            new Type('LongVariable')
        ));

        $this->assertTrue($result3->isMatch(
            new Location(
                new FileName('src/Domain/Analyser/internal/BaseLineResultsComparator.php'),
                new LineNumber(36)
            ),
            new Type('LongVariable')
        ));
    }

    /**
     * @deprecated https://trello.com/c/Lj8VCsbY
     */
    public function testConvertToString(): void
    {
        $dirtyActual = $this->phpmdResultsParser->convertToString($this->analysisResults);

        // Update timestamp to known value
        $actualAsString = preg_replace('/"timestamp":".{25}"/', '"timestamp":"2020-07-27T22:11:44+00:00"', $dirtyActual);

        $actualAsPrettyString = $this->convertToPrettyJson($actualAsString);

        $expected = $this->convertToPrettyJson($this->getResource('phpmd/phpmd.json'));

        $this->assertFileContentsSame($expected, $actualAsPrettyString);
    }

    private function convertToPrettyJson(string $dirtyJson): string
    {
        return json_encode(json_decode($dirtyJson, true), JSON_PRETTY_PRINT);
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phpmdResultsParser->showTypeGuessingWarning());
    }

    public function testGetIdentifier(): void
    {
        $this->assertEquals(new PhpmdJsonIdentifier(), $this->phpmdResultsParser->getIdentifier());
    }
}
