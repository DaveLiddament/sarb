<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use PHPUnit\Framework\TestCase;

class AbsoluteFileNameTest extends TestCase
{
    private const ABSOLUTE_FILENAME = '/tmp/file.php';

    public function testAbsolutePath(): void
    {
        $absoluteFileName = new AbsoluteFileName(self::ABSOLUTE_FILENAME);
        $this->assertSame(self::ABSOLUTE_FILENAME, $absoluteFileName->getFileName());
    }

    public function testRelativePath(): void
    {
        $this->expectException(InvalidPathException::class);
        new AbsoluteFileName('foo/bar.php');
    }
}
