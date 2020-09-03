<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\PreviousLocation;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use PHPUnit\Framework\TestCase;

class BaseLineAnalysisResultTest extends TestCase
{
    private const FILE_NAME_1 = 'fileName1';
    private const FILE_NAME_2 = 'fileName2';
    private const LINE_NUMBER_1 = 1;
    private const LINE_NUMBER_2 = 2;
    private const TYPE_1 = 'TYPE_1';
    private const TYPE_2 = 'TYPE_2';
    private const MESSAGE_1 = 'MESSAGE_1';

    /**
     * @var RelativeFileName
     */
    private $relativeFileName;

    /**
     * @var LineNumber
     */
    private $lineNumber;

    /**
     * @var Type
     */
    private $type;

    protected function setUp(): void
    {
        $this->relativeFileName = new RelativeFileName(self::FILE_NAME_1);
        $this->lineNumber = new LineNumber(self::LINE_NUMBER_1);
        $this->type = new Type(self::TYPE_1);
    }

    public function testBuild(): void
    {
        $baseLineAnalysisResult = $this->createBaseLineResult();

        $this->assertSame($this->relativeFileName, $baseLineAnalysisResult->getFileName());
        $this->assertSame($this->lineNumber, $baseLineAnalysisResult->getLineNumber());
        $this->assertSame($this->type, $baseLineAnalysisResult->getType());
        $this->assertSame(self::MESSAGE_1, $baseLineAnalysisResult->getMessage());
    }

    public function testConvertToAndFromArray(): void
    {
        $originalResult = $this->createBaseLineResult();

        $asArray = $originalResult->asArray();
        $unserialisedBaseLineAnalysisResult = BaseLineAnalysisResult::fromArray($asArray);

        // Should not be identical objects
        $this->assertNotSame($this->relativeFileName, $unserialisedBaseLineAnalysisResult->getFileName());
        $this->assertNotSame($this->lineNumber, $unserialisedBaseLineAnalysisResult->getLineNumber());
        $this->assertNotSame($this->type, $unserialisedBaseLineAnalysisResult->getType());

        // Values should be the same though
        $this->assertTrue($this->relativeFileName->isEqual($unserialisedBaseLineAnalysisResult->getFileName()));
        $this->assertTrue($this->lineNumber->isEqual($unserialisedBaseLineAnalysisResult->getLineNumber()));
        $this->assertTrue($this->type->isEqual($unserialisedBaseLineAnalysisResult->getType()));
        $this->assertSame(self::MESSAGE_1, $unserialisedBaseLineAnalysisResult->getMessage());
    }

    public function testIsActualMatch(): void
    {
        $baseLineAnalysisResult = $this->createBaseLineResult();
        $location = PreviousLocation::fromFileNameAndLineNumber($this->relativeFileName, $this->lineNumber);
        $this->assertTrue($baseLineAnalysisResult->isMatch($location, new Type(self::TYPE_1)));
    }

    public function testIsMatchDifferentType(): void
    {
        $baseLineAnalysisResult = $this->createBaseLineResult();
        $location = PreviousLocation::fromFileNameAndLineNumber($this->relativeFileName, $this->lineNumber);
        $this->assertFalse($baseLineAnalysisResult->isMatch($location, new Type(self::TYPE_2)));
    }

    public function testIsMatchDifferentFileName(): void
    {
        $baseLineAnalysisResult = $this->createBaseLineResult();
        $previousLocation = PreviousLocation::fromFileNameAndLineNumber(
            new RelativeFileName(self::FILE_NAME_2),
            $this->lineNumber
        );
        $this->assertFalse($baseLineAnalysisResult->isMatch($previousLocation, new Type(self::TYPE_1)));
    }

    public function testIsMatchDifferentLineNumber(): void
    {
        $baseLineAnalysisResult = $this->createBaseLineResult();
        $previousLocation = PreviousLocation::fromFileNameAndLineNumber(
            $this->relativeFileName,
            new LineNumber(self::LINE_NUMBER_2)
        );
        $this->assertFalse($baseLineAnalysisResult->isMatch($previousLocation, new Type(self::TYPE_1)));
    }

    /**
     * @psalm-return array<string,array{array<mixed>}>
     */
    public function invalidArrayDataProvider(): array
    {
        return [
            'missingLineNumber' => [
                [
                    'fileName' => self::FILE_NAME_1,
                    'type' => self::TYPE_1,
                    'message' => self::MESSAGE_1,
                ],
            ],
            'missingFileName' => [
                [
                    'lineNumber' => self::LINE_NUMBER_1,
                    'type' => self::TYPE_1,
                    'message' => self::MESSAGE_1,
                ],
            ],
            'missingType' => [
                [
                    'lineNumber' => self::LINE_NUMBER_1,
                    'fileName' => self::FILE_NAME_1,
                    'message' => self::MESSAGE_1,
                ],
            ],
            'missingMessage' => [
                [
                    'lineNumber' => self::LINE_NUMBER_1,
                    'fileName' => self::FILE_NAME_1,
                    'type' => self::TYPE_1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidArrayDataProvider
     * @psalm-param array<mixed> $asArray
     */
    public function testInvalidArray(array $asArray): void
    {
        $this->expectException(ArrayParseException::class);
        BaseLineAnalysisResult::fromArray($asArray);
    }

    private function createBaseLineResult(): BaseLineAnalysisResult
    {
        $baseLineAnalysisResult = BaseLineAnalysisResult::make(
            $this->relativeFileName,
            $this->lineNumber,
            $this->type,
            self::MESSAGE_1
        );

        return $baseLineAnalysisResult;
    }
}
