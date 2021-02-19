<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    public function testCreateFromAbsolutePath(): void
    {
        $projectRoot = new ProjectRoot('/sarb/root', '/sarb/root');
        $absoluteFileName = new AbsoluteFileName('/sarb/root/src/File.php');
        $lineNumber = new LineNumber(10);

        $location = Location::fromAbsoluteFileName($absoluteFileName, $projectRoot, $lineNumber);

        $this->assertSame($lineNumber, $location->getLineNumber());
        $this->assertSame('src/File.php', $location->getRelativeFileName()->getFileName());
    }
}
