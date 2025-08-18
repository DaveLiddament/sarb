<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\HistoryAnalyser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

final class DiffParserTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @return array<string,array{string, array<int,array{0: OriginalFileName|null, 1: NewFileName, 2: bool, 3: bool, 4: list<LineMutation>}>}>
     */
    public function dataProvider()
    {
        return [
            'fileChanged' => [
                'fileChanged.diff',
                [
                    [
                        new OriginalFileName('src/Person.php'),
                        new NewFileName('src/Person.php'),
                        false,
                        false,
                        [
                            LineMutation::newLineNumber(new LineNumber(18)),
                            LineMutation::newLineNumber(new LineNumber(19)),
                            LineMutation::newLineNumber(new LineNumber(20)),
                            LineMutation::newLineNumber(new LineNumber(21)),
                            LineMutation::newLineNumber(new LineNumber(22)),
                            LineMutation::originalLineNumber(new LineNumber(21)),
                            LineMutation::newLineNumber(new LineNumber(26)),
                            LineMutation::newLineNumber(new LineNumber(29)),
                            LineMutation::newLineNumber(new LineNumber(40)),
                            LineMutation::newLineNumber(new LineNumber(41)),
                            LineMutation::newLineNumber(new LineNumber(42)),
                            LineMutation::newLineNumber(new LineNumber(43)),
                            LineMutation::newLineNumber(new LineNumber(44)),
                            LineMutation::newLineNumber(new LineNumber(45)),
                            LineMutation::newLineNumber(new LineNumber(46)),
                            LineMutation::newLineNumber(new LineNumber(47)),
                        ],
                    ],
                ],
            ],

            'binaryFileAdded' => [
                'binaryFileAdded.diff',
                [
                    // No mutations expected
                ],
            ],

            'binaryFileChanged' => [
                'binaryFileChanged.diff',
                [
                    // No mutations expected
                ],
            ],

            'binaryFileDeleted' => [
                'binaryFileDeleted.diff',
                [
                    // No mutations expected
                ],
            ],

            'binaryFileRenamed' => [
                'binaryFileRenamed.diff',
                [
                    [
                        new OriginalFileName('img.png'),
                        new NewFileName('img1.png'),
                        false,
                        false,
                        [],
                    ],
                ],
            ],

            'fileAdded' => [
                'fileAdded.diff',
                [
                    [
                        null,
                        new NewFileName('src/Person.php'),
                        true,
                        false,
                        [],
                    ],
                ],
            ],

            'fileRenamed' => [
                'fileRenamed.diff',
                [
                    [
                        new OriginalFileName('src/Printer.php'),
                        new NewFileName('src/Foo.php'),
                        false,
                        false,
                        [],
                    ],
                ],
            ],

            'fileDeleted' => [
                'fileDeleted.diff',
                [
                    // Deleted files are not added to FileMutations as we don't care about them
                ],
            ],

            'fileRenamedAndChanged' => [
                'fileRenamedAndChanged.diff',
                [
                    [
                        new OriginalFileName('src/User.php'),
                        new NewFileName('src/Person.php'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(9)),
                            LineMutation::newLineNumber(new LineNumber(9)),
                            LineMutation::originalLineNumber(new LineNumber(23)),
                            LineMutation::newLineNumber(new LineNumber(23)),
                        ],
                    ],
                ],
            ],

            'file1RenameFile2Changed' => [
                'file1RenameFile2Changed.diff',
                [
                    [
                        new OriginalFileName('src/Bar.php'),
                        new NewFileName('src/Baz.php'),
                        false,
                        false,
                        [],
                    ],
                    [
                        new OriginalFileName('src/User.php'),
                        new NewFileName('src/User.php'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(8)),
                        ],
                    ],
                ],
            ],

            '2filesChanged' => [
                '2filesChanged.diff',
                [
                    [
                        new OriginalFileName('src/Baz.php'),
                        new NewFileName('src/Baz.php'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(5)),
                        ],
                    ],
                    [
                        new OriginalFileName('src/User.php'),
                        new NewFileName('src/User.php'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(5)),
                        ],
                    ],
                ],
            ],
            '1lineFileChanged' => [
                '1lineFileChanged.diff',
                [
                    [
                        new OriginalFileName('message.txt'),
                        new NewFileName('message.txt'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(1)),
                            LineMutation::newLineNumber(new LineNumber(1)),
                        ],
                    ],
                ],
            ],
            '1lineTo2LinesFileChanged' => [
                '1lineTo2LinesFileChanged.diff',
                [
                    [
                        new OriginalFileName('message.txt'),
                        new NewFileName('message.txt'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(1)),
                            LineMutation::newLineNumber(new LineNumber(1)),
                            LineMutation::newLineNumber(new LineNumber(2)),
                        ],
                    ],
                ],
            ],
            '2lineTo1LineFileChanged' => [
                '2lineTo1LineFileChanged.diff',
                [
                    [
                        new OriginalFileName('message.txt'),
                        new NewFileName('message.txt'),
                        false,
                        false,
                        [
                            LineMutation::originalLineNumber(new LineNumber(1)),
                            LineMutation::originalLineNumber(new LineNumber(2)),
                            LineMutation::newLineNumber(new LineNumber(1)),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array<int,array{0: OriginalFileName|null, 1: NewFileName, 2: bool, 3: bool, 4: list<LineMutation>}> $expectedFileMutations
     */
    public function testDiffParser(string $inputFile, array $expectedFileMutations): void
    {
        $diffAsString = $this->getResource("validDiffs/$inputFile");

        $parser = new Parser();
        $fileMutations = $parser->parseDiff($diffAsString);

        // Add assertion so we don't get risky assertion warnings for diffs with no mutations
        $expectedFileMutationsCount = count($expectedFileMutations);
        $actualFileMutationsCount = $fileMutations->getCount();

        $this->assertSame($expectedFileMutationsCount, $actualFileMutationsCount);

        foreach ($expectedFileMutations as [$originalFileName, $newFileName, $isAdded, $isDeleted, $lineMutations]) {
            $actualFileMutation = $fileMutations->getFileMutation($newFileName);
            $this->assertNotNull($actualFileMutation, "No FileMutation for [{$newFileName->getFileName()}]");

            $this->assertSame($isAdded, $actualFileMutation->isAddedFile());
            $this->assertSame($isDeleted, $actualFileMutation->isDeletedFile());

            if (!$isAdded) {
                $this->assertOriginalFileNameSame($actualFileMutation->getOriginalFileName(), $originalFileName);
            }

            $this->assertLineMutations($actualFileMutation->getLineMutations(), $lineMutations);
        }
    }

    private function assertOriginalFileNameSame(?OriginalFileName $a, ?OriginalFileName $b): void
    {
        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertSame($a->getFileName(), $b->getFileName());
    }

    /**
     * @param list<LineMutation> $expectedLineMutations
     * @param list<LineMutation> $actualLineMutations
     */
    private function assertLineMutations(array $expectedLineMutations, array $actualLineMutations): void
    {
        $this->assertCount(count($expectedLineMutations), $actualLineMutations);
        foreach ($expectedLineMutations as $i => $expectedLineMutation) {
            $actualLineMutation = $actualLineMutations[$i];
            $this->assertLineMutation($expectedLineMutation, $actualLineMutation, $i);
        }
    }

    private function assertLineMutation(
        ?LineMutation $expectedLineMutation,
        ?LineMutation $actualLineMutation,
        int $i,
    ): void {
        if (null === $expectedLineMutation) {
            $this->assertNull($actualLineMutation, "Error with line mutation [$i]");

            return;
        }

        $this->assertTrue($expectedLineMutation->isEqual($actualLineMutation), "Error with line mutation [$i]");
    }
}
