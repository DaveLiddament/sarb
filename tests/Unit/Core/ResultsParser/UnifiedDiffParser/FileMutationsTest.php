<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\FileMutation;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\FileMutations;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\UnifiedDiffParser\OriginalFileName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileMutationsTest extends TestCase
{
    private const FILE_1_NAME = 'foo/bar.php';
    private const FILE_2_NAME = 'foo/baz.php';
    private const FILE_3_NAME = 'foo/foo.php';

    public function testHappyPath(): void
    {
        $fileMutation1 = new FileMutation(
            new OriginalFileName(self::FILE_1_NAME),
            new NewFileName(self::FILE_1_NAME),
            []
        );

        $fileMutation2 = new FileMutation(
            new OriginalFileName(self::FILE_2_NAME),
            new NewFileName(self::FILE_2_NAME),
            []
        );

        $fileMutations = new FileMutations([$fileMutation1, $fileMutation2]);

        $this->assertSame($fileMutation1, $fileMutations->getFileMutation(new NewFileName(self::FILE_1_NAME)));
        $this->assertSame($fileMutation2, $fileMutations->getFileMutation(new NewFileName(self::FILE_2_NAME)));
        $this->assertNull($fileMutations->getFileMutation(new NewFileName(self::FILE_3_NAME)));
    }

    public function testDuplicateFilesMutationsAdded(): void
    {
        $fileMutation1 = new FileMutation(
            new OriginalFileName(self::FILE_1_NAME),
            new NewFileName(self::FILE_1_NAME),
            []
        );

        $fileMutation2 = new FileMutation(
            new OriginalFileName(self::FILE_1_NAME),
            new NewFileName(self::FILE_1_NAME),
            []
        );

        $this->expectException(InvalidArgumentException::class);
        new FileMutations([$fileMutation1, $fileMutation2]);
    }
}
