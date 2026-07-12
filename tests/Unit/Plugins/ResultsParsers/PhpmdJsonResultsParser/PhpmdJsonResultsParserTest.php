<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

final class PhpmdJsonResultsParserTest extends TestCase
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
        $projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');

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
            'LongVariable',
            Severity::error(),
        );
        $this->assertSame(
            'Avoid excessively long variable names like $latestAnalysisResults. Keep variable name length under 20.',
            $result1->getMessage(),
        );

        $this->assertMatch($result2,
            'src/Domain/Analyser/BaseLineResultsRemover.php',
            30,
            'LongVariable',
            Severity::error(),
        );

        $this->assertMatch($result3,
            'src/Domain/Analyser/internal/BaseLineResultsComparator.php',
            36,
            'LongVariable',
            Severity::error(),
        );
    }

    public function testPriorityMapping(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $original = $this->getResource('phpmd/phpmd_priorities.json');
        $analysisResults = $this->phpmdResultsParser->convertFromString($original, $projectRoot);

        $this->assertCount(4, $analysisResults->getAnalysisResults());

        [$result1, $result2, $result3, $result4] = $analysisResults->getAnalysisResults();

        // PHPMD priorities 1-3 are imported as errors, 4-5 as warnings
        $this->assertMatch($result1, 'src/Foo.php', 10, 'HighPriorityRule', Severity::error());
        $this->assertMatch($result2, 'src/Foo.php', 20, 'NormalPriorityRule', Severity::error());
        $this->assertMatch($result3, 'src/Foo.php', 30, 'LowPriorityRule', Severity::warning());
        $this->assertMatch($result4, 'src/Foo.php', 40, 'InformationRule', Severity::warning());
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
