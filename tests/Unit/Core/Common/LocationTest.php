<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    public function testCreateFromAbsolutePath(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/sarb/root');
        $absoluteFileName = new AbsoluteFileName('/sarb/root/src/File.php');
        $lineNumber = new LineNumber(10);

        $location = Location::fromAbsoluteFileName($absoluteFileName, $projectRoot, $lineNumber);

        $this->assertSame($lineNumber, $location->getLineNumber());
        $this->assertSame('src/File.php', $location->getRelativeFileName()->getFileName());
        $this->assertSame('/sarb/root/src/File.php', $location->getAbsoluteFileName()->getFileName());
    }

    public function testCreateFromRelativePath(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/sarb/root');
        $relativeFileName = new RelativeFileName('src/File.php');
        $lineNumber = new LineNumber(10);

        $location = Location::fromRelativeFileName($relativeFileName, $projectRoot, $lineNumber);

        $this->assertSame($lineNumber, $location->getLineNumber());
        $this->assertSame('src/File.php', $location->getRelativeFileName()->getFileName());
        $this->assertSame('/sarb/root/src/File.php', $location->getAbsoluteFileName()->getFileName());
    }

    public function testCreateFromRelativePathWithRelativePathToProjectRoot(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/sarb/root');
        $projectRoot = $projectRoot->withRelativePath('code');
        $relativeFileName = new RelativeFileName('src/File.php');
        $lineNumber = new LineNumber(10);

        $location = Location::fromRelativeFileName($relativeFileName, $projectRoot, $lineNumber);

        $this->assertSame($lineNumber, $location->getLineNumber());
        $this->assertSame('src/File.php', $location->getRelativeFileName()->getFileName());
        $this->assertSame('/sarb/root/code/src/File.php', $location->getAbsoluteFileName()->getFileName());
    }
}
