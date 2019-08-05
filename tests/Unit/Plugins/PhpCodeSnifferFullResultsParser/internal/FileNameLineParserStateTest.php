<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FileNameLineParserState;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal\FirstLineParserState;
use PHPUnit\Framework\TestCase;

class FileNameLineParserStateTest extends TestCase
{
    private const FILE_NAME_LINE = 'FILE: /vagrant/src/Domain/Common/InvalidPathException.php';

    /**
     * @var FileNameLineParserState
     */
    private $fileNameLineParserState;

    protected function setUp(): void
    {
        $projectRoot = new ProjectRoot('/vagrant', '/home');
        $this->fileNameLineParserState = new FileNameLineParserState(new AnalysisResults(), $projectRoot);
    }

    public function testIsFileName(): void
    {
        $nextLineParserState = $this->fileNameLineParserState->parseLine(self::FILE_NAME_LINE);
        $this->assertInstanceOf(FirstLineParserState::class, $nextLineParserState);
    }

    public function testNotFileName(): void
    {
        $nextLineParserState = $this->fileNameLineParserState->parseLine('---');
        $this->assertSame($this->fileNameLineParserState, $nextLineParserState);
    }
}
