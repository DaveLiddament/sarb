<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use PHPUnit\Framework\TestCase;

final class AnalysisResultTest extends TestCase
{
    private const FILE_NAME = '/tmp/foo.php';
    private const LINE_NUMBER = 10;
    private const TYPE = 'TYPE';
    private const LEGACY_TYPE = 'LEGACY_TYPE';
    private const MESSAGE = 'message';
    private const FULL_DETAILS = [
        'snippet' => 'class Foo',
    ];

    public function testHappyPath(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');

        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName(self::FILE_NAME),
            $projectRoot,
            new LineNumber(self::LINE_NUMBER),
        );

        $analysisResult = new AnalysisResult(
            $location,
            new Type(self::TYPE),
            self::MESSAGE,
            self::FULL_DETAILS,
            Severity::error(),
        );

        $this->assertSame(self::FILE_NAME, $analysisResult->getLocation()->getAbsoluteFileName()->getFileName());
        $this->assertSame(self::LINE_NUMBER, $analysisResult->getLocation()->getLineNumber()->getLineNumber());
        $this->assertSame(self::TYPE, $analysisResult->getType()->getType());
        $this->assertSame(self::MESSAGE, $analysisResult->getMessage());
        $this->assertSame(self::FULL_DETAILS, $analysisResult->getFullDetails());
        $this->assertNull($analysisResult->getLegacyType());
    }

    public function testWithLegacyType(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');

        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName(self::FILE_NAME),
            $projectRoot,
            new LineNumber(self::LINE_NUMBER),
        );

        $analysisResult = new AnalysisResult(
            $location,
            new Type(self::TYPE),
            self::MESSAGE,
            self::FULL_DETAILS,
            Severity::error(),
            new Type(self::LEGACY_TYPE),
        );

        $this->assertSame(self::TYPE, $analysisResult->getType()->getType());
        $legacyType = $analysisResult->getLegacyType();
        $this->assertNotNull($legacyType);
        $this->assertSame(self::LEGACY_TYPE, $legacyType->getType());

        // The baseline entry should hold the (primary) type, not the legacy type
        $baseLineAnalysisResult = $analysisResult->asBaseLineAnalysisResult();
        $this->assertSame(self::TYPE, $baseLineAnalysisResult->getType()->getType());
    }
}
