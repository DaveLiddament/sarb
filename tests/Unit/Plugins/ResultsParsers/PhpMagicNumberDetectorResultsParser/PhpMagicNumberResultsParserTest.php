<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpMagicNumberDetectorResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpMagicNumberDetectorResultsParser\PhpMagicNumberDetectorIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpMagicNumberDetectorResultsParser\PhpMagicNumberDetectorResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertFileContentsSameTrait;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AssertResultMatch;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class PhpMagicNumberResultsParserTest extends TestCase
{
    use AssertFileContentsSameTrait;
    use AssertResultMatch;
    use ResourceLoaderTrait;

    /**
     * @var AnalysisResults
     */
    private $analysisResults;

    /**
     * @var PhpMagicNumberDetectorResultsParser
     */
    private $phpMagicNumberDetectorResultsParser;

    protected function setUp(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        $this->phpMagicNumberDetectorResultsParser = new PhpMagicNumberDetectorResultsParser();
        $original = $this->getResource('phpmnd/phpmnd.txt');

        // Convert both ways
        $this->analysisResults = $this->phpMagicNumberDetectorResultsParser->convertFromString($original, $projectRoot);
    }

    public function testConversion(): void
    {
        $this->assertCount(3, $this->analysisResults->getAnalysisResults());

        $result1 = $this->analysisResults->getAnalysisResults()[0];
        $result2 = $this->analysisResults->getAnalysisResults()[1];
        $result3 = $this->analysisResults->getAnalysisResults()[2];

        $this->assertMatch($result1,
            'src/File1.php',
            6,
            '123',
            Severity::error(),
        );
        $this->assertSame(
            'Magic number 123',
            $result1->getMessage(),
        );

        $this->assertMatch($result2,
            'src/foo/bar/File 2.php',
            11,
            '1.7',
            Severity::error(),
        );

        $this->assertMatch($result3,
            'tests/MyTest1.php',
            16,
            '10',
            Severity::error(),
        );
    }

    public function testTypeGuesser(): void
    {
        $this->assertFalse($this->phpMagicNumberDetectorResultsParser->showTypeGuessingWarning());
    }

    public function testGetIdentifier(): void
    {
        $this->assertEquals(
            new PhpMagicNumberDetectorIdentifier(),
            $this->phpMagicNumberDetectorResultsParser->getIdentifier(),
        );
    }
}
