<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileWriter;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
    /**
     * @var FileWriter
     */
    private $fileWriter;

    private const DATA = [
        'NAME' => 'SARB',
    ];

    protected function setUp(): void
    {
        $this->fileWriter = new FileWriter();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testWriteArrayToFile(): void
    {
        $fileName = $this->getFileName('write_file.json');
        $this->fileWriter->writeArrayToFile($fileName, self::DATA);
    }

    public function testWriteInvalidFile(): void
    {
        $fileName = $this->getFileName('invaliddirectory/asdfasd/file.json');
        $this->expectException(FileAccessException::class);
        $this->fileWriter->writeArrayToFile($fileName, self::DATA);
    }

    private function getFileName(string $file): FileName
    {
        $fullUrl = __DIR__.'/../../../scratchpad/'.$file;

        return new FileName($fullUrl);
    }
}
