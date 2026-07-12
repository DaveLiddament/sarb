<?php

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

final class PhpMagicNumberResultsParserTest extends TestCase
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

    public function testCrlfLineEndings(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');

        // Tool output line endings depend on the OS the tool ran on
        $original = "src/File1.php:6. Magic number: 123\r\nsrc/File3.php:8. Magic number: 42\r\n";
        $analysisResults = $this->phpMagicNumberDetectorResultsParser->convertFromString($original, $projectRoot);

        $this->assertCount(2, $analysisResults->getAnalysisResults());

        $result1 = $analysisResults->getAnalysisResults()[0];
        $result2 = $analysisResults->getAnalysisResults()[1];

        // The magic number (used as the type) must not include the carriage return
        $this->assertMatch($result1, 'src/File1.php', 6, '123', Severity::error());
        $this->assertMatch($result2, 'src/File3.php', 8, '42', Severity::error());
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
