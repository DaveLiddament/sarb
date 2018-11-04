<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\ParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;

class InvalidDiffParserTest extends TestCase
{
    use ResourceLoaderTrait;

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
     *
     * @param string $inputFile
     * @param string $location
     * @param string $reason
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
