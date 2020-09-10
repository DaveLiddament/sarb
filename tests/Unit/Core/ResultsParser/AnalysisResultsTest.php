<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\AnalysisResultsAdderTrait;
use PHPUnit\Framework\TestCase;

class AnalysisResultsTest extends TestCase
{
    use AnalysisResultsAdderTrait;
    private const FILE_A = '/FILE_A';
    private const FILE_B = '/FILE_B';
    private const LINE_1 = 1;
    private const LINE_2 = 2;
    private const TYPE = 'TYPE_A';
    private const FULL_DETAILS = ['snippet' => 'class Foo'];

    /**
     * @var AnalysisResultsBuilder
     */
    private $analysisResultsBuilder;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    protected function setUp(): void
    {
        $this->analysisResultsBuilder = new AnalysisResultsBuilder();
        $this->projectRoot = new ProjectRoot('/', '/');
    }

    public function testNoResults(): void
    {
        $analysisResults = $this->analysisResultsBuilder->build();
        $this->assertTrue($analysisResults->hasNoIssues());
        $this->assertSame(0, $analysisResults->getCount());
        $this->assertSame([], $analysisResults->getAnalysisResults());
    }

    public function test1AnalysisResult(): void
    {
        $analysisResult = $this->buildAnalysisResult(
            $this->projectRoot,
            self::FILE_A,
            self::LINE_1,
            self::TYPE,
            self::FULL_DETAILS
        );
        $this->analysisResultsBuilder->addAnalysisResult($analysisResult);
        $analysisResults = $this->analysisResultsBuilder->build();
        $this->assertFalse($analysisResults->hasNoIssues());
        $this->assertSame(1, $analysisResults->getCount());
        $this->assertSame([$analysisResult], $analysisResults->getAnalysisResults());
    }

    public function test3AnalysisResultsAreOrderedCorrectly(): void
    {
        $analysisResult1 = $this->buildAnalysisResult($this->projectRoot, self::FILE_A, self::LINE_1, self::TYPE);
        $analysisResult2 = $this->buildAnalysisResult($this->projectRoot, self::FILE_A, self::LINE_2, self::TYPE);
        $analysisResult3 = $this->buildAnalysisResult($this->projectRoot, self::FILE_B, self::LINE_1, self::TYPE);

        // Add results in none expected order
        $this->analysisResultsBuilder->addAnalysisResult($analysisResult2);
        $this->analysisResultsBuilder->addAnalysisResult($analysisResult3);
        $this->analysisResultsBuilder->addAnalysisResult($analysisResult1);

        $analysisResults = $this->analysisResultsBuilder->build();
        $this->assertFalse($analysisResults->hasNoIssues());
        $this->assertSame(3, $analysisResults->getCount());
        $this->assertSame([
            $analysisResult1,
            $analysisResult2,
            $analysisResult3,
        ], $analysisResults->getAnalysisResults());
    }
}
