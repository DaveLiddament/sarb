<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineAnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use PHPUnit\Framework\TestCase;

class BaseLineAnalysisResultsTest extends TestCase
{
    private const PROJECT_ROOT = '/';
    private const ABSOLUTE_FILE_NAME_1 = '/fileName1';
    private const FILE_NAME_1 = 'fileName1';
    private const LINE_NUMBER_1 = 1;
    private const TYPE_1 = 'TYPE_1';
    private const MESSAGE_1 = 'MESSAGE_1';

    public function testNoBaseLineResults(): void
    {
        $baseLineResults = BaseLineAnalysisResults::fromArray([]);
        $this->assertEmpty($baseLineResults->asArray());
        $this->assertEmpty($baseLineResults->getBaseLineAnalysisResults());
        $this->assertSame(0, $baseLineResults->getCount());
    }

    public function test1BaseLineResult(): void
    {
        $baseLineResults = BaseLineAnalysisResults::fromArray([
            [
                'fileName' => self::FILE_NAME_1,
                'lineNumber' => self::LINE_NUMBER_1,
                'type' => self::TYPE_1,
                'message' => self::MESSAGE_1,
            ],
        ]);

        $this->assertSame(1, $baseLineResults->getCount());
        $this->assertCount(1, $baseLineResults->getBaseLineAnalysisResults());
        $this->assertCount(1, $baseLineResults->asArray());

        $baseLineResult = $baseLineResults->getBaseLineAnalysisResults()[0];
        $this->assertSame(self::FILE_NAME_1, $baseLineResult->getFileName()->getFileName());
        $this->assertSame(self::LINE_NUMBER_1, $baseLineResult->getLineNumber()->getLineNumber());
        $this->assertSame(self::TYPE_1, $baseLineResult->getType()->getType());
        $this->assertSame(self::MESSAGE_1, $baseLineResult->getMessage());
    }

    /**
     * @psalm-return array<string,array{string,array<mixed>}>
     */
    public function invalidDataProvider(): array
    {
        return [
            'notArray' => [
                'Issue with result [1]. Value for [base level] is not of the expected type [array]',
                [
                    'a string',
                ],
            ],
            'invalidBaseLineResult' => [
                'Issue with result [1]. Missing key [lineNumber]',
                [
                    [
                        'fileName' => self::FILE_NAME_1,
                        'type' => self::TYPE_1,
                        'message' => self::MESSAGE_1,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     * @psalm-param array<mixed> $array
     */
    public function testInvalidArrayData(string $exceptionMessage, array $array): void
    {
        $this->expectException(ParseAtLocationException::class);
        $this->expectExceptionMessage($exceptionMessage);
        BaseLineAnalysisResults::fromArray($array);
    }

    public function testCreateFromAnalysisResults(): void
    {
        $lineNumber = new LineNumber(self::LINE_NUMBER_1);
        $type = new Type(self::TYPE_1);
        $analysisResultsBuilder = new AnalysisResultsBuilder();

        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(self::PROJECT_ROOT);
        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName(self::ABSOLUTE_FILE_NAME_1),
            $projectRoot,
            $lineNumber
        );

        $analysisResultsBuilder->addAnalysisResult(new AnalysisResult(
            $location,
            $type,
            self::MESSAGE_1,
            [],
            Severity::error()
        ));

        $baseLineResults = BaseLineAnalysisResults::fromAnalysisResults($analysisResultsBuilder->build());
        $this->assertCount(1, $baseLineResults->getBaseLineAnalysisResults());
        $baseLineResult = $baseLineResults->getBaseLineAnalysisResults()[0];

        $this->assertSame(self::FILE_NAME_1, $baseLineResult->getFileName()->getFileName());
        $this->assertSame($lineNumber, $baseLineResult->getLineNumber());
        $this->assertSame($type, $baseLineResult->getType());
        $this->assertSame(self::MESSAGE_1, $baseLineResult->getMessage());
    }
}
