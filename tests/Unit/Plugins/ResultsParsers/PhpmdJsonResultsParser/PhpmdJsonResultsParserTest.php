<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpmdJsonResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
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

        $this->assertMatch($result1,
            'src/Domain/Analyser/BaseLineResultsRemover.php',
            28,
            'LongVariable'
        );
        $this->assertSame(
            'Avoid excessively long variable names like $latestAnalysisResults. Keep variable name length under 20.',
            $result1->getMessage()
        );

        $this->assertMatch($result2,
            'src/Domain/Analyser/BaseLineResultsRemover.php',
            30,
            'LongVariable'
        );

        $this->assertMatch($result3,
            'src/Domain/Analyser/internal/BaseLineResultsComparator.php',
            36,
            'LongVariable'
        );
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
