<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use PHPUnit\Framework\TestCase;

class AnalysisResultTest extends TestCase
{
    private const FILE_NAME = '/tmp/foo.php';
    private const LINE_NUMBER = 10;
    private const TYPE = 'TYPE';
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
            new LineNumber(self::LINE_NUMBER)
        );

        $analysisResult = new AnalysisResult($location, new Type(self::TYPE), self::MESSAGE, self::FULL_DETAILS);

        $this->assertSame(self::FILE_NAME, $analysisResult->getLocation()->getAbsoluteFileName()->getFileName());
        $this->assertSame(self::LINE_NUMBER, $analysisResult->getLocation()->getLineNumber()->getLineNumber());
        $this->assertSame(self::TYPE, $analysisResult->getType()->getType());
        $this->assertSame(self::MESSAGE, $analysisResult->getMessage());
        $this->assertSame(self::FULL_DETAILS, $analysisResult->getFullDetails());
    }
}
