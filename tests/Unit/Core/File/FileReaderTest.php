<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\File;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileAccessException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidContentTypeException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

final class FileReaderTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @var FileReader
     */
    private $fileReader;

    protected function setUp(): void
    {
        $this->fileReader = new FileReader();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testReadJsonFile(): void
    {
        $fileName = $this->getFileName('valid-json.json');
        $this->fileReader->readJsonFile($fileName);
    }

    public function testReadInvalidJsonFile(): void
    {
        $fileName = $this->getFileName('invalid-json.json');
        $this->expectException(InvalidContentTypeException::class);
        $this->fileReader->readJsonFile($fileName);
    }

    public function testReadInvalidFile(): void
    {
        $fileName = $this->getFileName('none-existant-file.json');
        $this->expectException(FileAccessException::class);
        $this->fileReader->readJsonFile($fileName);
    }
}
