<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;
use Throwable;

class InvalidPhpmdFileFormatTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');
    }

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
        ];
    }

    /**
     * @dataProvider filenameDataProvider
     */
    public function testInvalidFileFormat(string $fileName): void
    {
        $this->assertExceptionThrown(ParseAtLocationException::class, $fileName);
    }

    public function testNotJsonFileSupplied(): void
    {
        $this->assertExceptionThrown(InvalidContentTypeException::class, 'not.json');
    }

    /**
     * @psalm-param class-string<Throwable> $exceptionType
     */
    private function assertExceptionThrown(string $exceptionType, string $fileName): void
    {
        $fileContents = $this->getResource("phpmd/$fileName");
        $projectRoot = ProjectRoot::fromProjectRoot('/vagrant/static-analysis-baseliner', '/home');
        $phpmdResultsParser = new PhpmdJsonResultsParser();

        $this->expectException($exceptionType);
        $phpmdResultsParser->convertFromString($fileContents, $projectRoot);
    }
}
