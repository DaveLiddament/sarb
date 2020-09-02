<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class InvalidPhpmdFileFormatTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @psalm-return array<int,array{string}>
     */
    public function filenameDataProvider(): array
    {
        return [
            [
                'phpmd_missing_file.json',
            ],
            [
                'phpmd_missing_files.json',
            ],
            [
                'phpmd_missing_violations.json',
            ],
            [
                'phpmd_invalid_violation.json',
            ],
            [
                'not.json',
            ],
        ];
    }

    /**
     * @dataProvider filenameDataProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $fileContents = $this->getResource("phpmd/$fileName");
        $projectRoot = new ProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $phpmdResultsParser = new PhpmdJsonResultsParser();

        $this->expectException(InvalidFileFormatException::class);
        $phpmdResultsParser->convertFromString($fileContents, $projectRoot);
    }
}
