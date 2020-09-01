<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\internal\FileMutationsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\UnifiedDiffParser\OriginalFileName;
use PHPUnit\Framework\TestCase;

/**
 * Tests to check the FileMutationsBuilder and FileMutationBuilder both work.
 */
class BuilderTest extends TestCase
{
    private const FILE_1_NAME = 'foo/bar.php';
    private const FILE_2_NAME = 'foo/baz.php';
    private const FILE_3_NAME = 'foo/foo.php';

    public function testBuildersHappyPath(): void
    {
        $fileMutationsBuilder = new FileMutationsBuilder();

        $file1OriginalName = new OriginalFileName(self::FILE_1_NAME);
        $file1NewName = new NewFileName(self::FILE_1_NAME);
        $fileMutationBuilder1 = new FileMutationBuilder($fileMutationsBuilder);
        $fileMutationBuilder1->setOriginalFileName($file1OriginalName);
        $fileMutationBuilder1->setNewFileName($file1NewName);
        $fileMutationBuilder1->addLineMutation(LineMutation::newLineNumber(new LineNumber(1)));
        $fileMutationBuilder1->addLineMutation(LineMutation::originalLineNumber(new LineNumber(2)));
        $fileMutationBuilder1->build();

        $file2OriginalName = new OriginalFileName(self::FILE_2_NAME);
        $file2NewName = new NewFileName(self::FILE_3_NAME);
        $fileMutationBuilder2 = new FileMutationBuilder($fileMutationsBuilder);
        $fileMutationBuilder2->setOriginalFileName($file2OriginalName);
        $fileMutationBuilder2->setNewFileName($file2NewName);
        $fileMutationBuilder2->build();

        $fileMutations = $fileMutationsBuilder->build();

        $fileMutation1 = $fileMutations->getFileMutation($file1NewName);
        $this->assertNotNull($fileMutation1);
        $this->assertSame($file1OriginalName, $fileMutation1->getOriginalFileName());
        $this->assertSame($file1NewName, $fileMutation1->getNewFileName());

        $lineMutations = $fileMutation1->getLineMutations();
        $this->assertCount(2, $lineMutations);
        $expectedLineMutation1 = LineMutation::newLineNumber(new LineNumber(1));
        $this->assertTrue($expectedLineMutation1->isEqual($lineMutations[0]));

        $fileMutation2 = $fileMutations->getFileMutation($file2NewName);
        $this->assertNotNull($fileMutation2);
        $this->assertSame($file2OriginalName, $fileMutation2->getOriginalFileName());
        $this->assertSame($file2NewName, $fileMutation2->getNewFileName());
        $this->assertCount(0, $fileMutation2->getLineMutations());
    }
}
