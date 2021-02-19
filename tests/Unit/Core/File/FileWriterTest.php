<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
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
        $fileName = $this->getBaseLineFileName('write_file.json');
        $this->fileWriter->writeArrayToFile($fileName, self::DATA);
    }

    public function testWriteInvalidFile(): void
    {
        $fileName = $this->getBaseLineFileName('invaliddirectory/asdfasd/file.json');
        $this->expectException(FileAccessException::class);
        $this->fileWriter->writeArrayToFile($fileName, self::DATA);
    }

    private function getBaseLineFileName(string $file): BaseLineFileName
    {
        $fullUrl = __DIR__.'/../../../scratchpad/'.$file;

        return new BaseLineFileName($fullUrl);
    }
}
