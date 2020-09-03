<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\HistoryAnalyser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\FileMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\OriginalFileName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileMutationTest extends TestCase
{
    private const NEW_TXT = 'new.txt';
    private const ORIGINAL_TXT = 'original.txt';

    public function testInvalidSetup(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FileMutation(null, null, []);
    }

    public function testAddedFileMutation(): void
    {
        $newFileName = new NewFileName(self::NEW_TXT);
        $fileMutation = new FileMutation(null, $newFileName, []);
        $this->assertSame($newFileName, $fileMutation->getNewFileName());
        $this->assertTrue($fileMutation->isAddedFile());
        $this->assertFalse($fileMutation->isDeletedFile());
    }

    public function testDeletedFileMutation(): void
    {
        $originalFileName = new OriginalFileName(self::ORIGINAL_TXT);
        $fileMutation = new FileMutation($originalFileName, null, []);
        $this->assertSame($originalFileName, $fileMutation->getOriginalFileName());
        $this->assertFalse($fileMutation->isAddedFile());
        $this->assertTrue($fileMutation->isDeletedFile());
    }

    public function testFileMutation(): void
    {
        $newFileName = new NewFileName(self::NEW_TXT);
        $originalFileName = new OriginalFileName(self::ORIGINAL_TXT);
        $fileMutation = new FileMutation($originalFileName, $newFileName, []);
        $this->assertSame($newFileName, $fileMutation->getNewFileName());
        $this->assertFalse($fileMutation->isAddedFile());
        $this->assertFalse($fileMutation->isDeletedFile());
        $this->assertSame([], $fileMutation->getLineMutations());
    }
}
