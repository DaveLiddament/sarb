<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\HistoryAnalyser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\ParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

final class InvalidDiffParserTest extends TestCase
{
    use ResourceLoaderTrait;

    /**
     * @psalm-return array<string,array{string,string,string}>
     */
    public function dataProvider()
    {
        return [
            'missingRenameTo' => [
                'missingRenameTo.diff',
                '4',
                'NO_RENAME_TO',
            ],

            'truncateBeforeRenameTo' => [
                'truncateBeforeRenameTo.diff',
                ParseException::UNEXPECTED_END_OF_FILE,
                'NO_RENAME_TO',
            ],

            'truncateBeforeNewFile' => [
                'truncatedBeforeNewFile.diff',
                ParseException::UNEXPECTED_END_OF_FILE,
                'NO_NEW_FILE_NAME',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testInvalidDiff(string $inputFile, string $location, string $reason): void
    {
        $diffAsString = $this->getResource("invalidDiffs/$inputFile");

        $parser = new Parser();
        try {
            $parser->parseDiff($diffAsString);
            $this->fail('Expected ParseException');
        } catch (ParseException $e) {
            $this->assertSame($location, $e->getLocation());
            $this->assertSame($reason, $e->getReason());
        }
    }
}
