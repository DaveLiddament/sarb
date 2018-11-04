<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\LineMutation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\NewFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\OriginalFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class DiffParserTest extends TestCase
{
    use ResourceLoaderTrait;

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
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $inputFile
     * @param array $expectedFileMutations
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

        /**
         * @var OriginalFileName
         * @var NewFileName $newFileName
         * @var array $lineMutations
         */
        foreach ($expectedFileMutations as list($originalFileName, $newFileName, $isAdded, $isDeleted, $lineMutations)) {
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
        if (null === $a) {
            $this->assertNull($b);
        }

        $this->assertSame($a->getFileName(), $b->getFileName());
    }

    private function assertLineMutations(array $expectedLineMutations, array $actualLineMutations): void
    {
        $this->assertSame(count($expectedLineMutations), count($actualLineMutations));
        foreach ($expectedLineMutations as $i => $expectedLineMutation) {
            $actualLineMutation = $actualLineMutations[$i];
            $this->assertLineMutation($expectedLineMutation, $actualLineMutation, $i);
        }
    }

    private function assertLineMutation(
        ?LineMutation $expectedLineMutation,
        ?LineMutation $actualLineMutation,
        int $i
    ): void {
        if (null === $expectedLineMutation) {
            $this->assertNull($actualLineMutation, "Error with line mutation [$i]");

            return;
        }

        $this->assertTrue($expectedLineMutation->isEqual($actualLineMutation), "Error with line mutation [$i]");
    }
}
