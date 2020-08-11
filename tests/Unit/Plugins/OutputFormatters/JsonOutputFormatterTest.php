<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\JsonOutputFormatter;

class JsonOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('json');
    }

    public function testNoIssues(): void
    {
        $expectedOutput = <<<EOF
{
    "summary": {
        "latestAnalysisCount": 2,
        "baseLineCount": 4,
        "baseLineRemovedCount": 0
    },
    "issues": [],
    "success": true
}
EOF;

        $this->assertNoIssuesOutput($expectedOutput);
    }

    public function testWithIssues(): void
    {
        $expectedOuput = <<<EOF
{
    "summary": {
        "latestAnalysisCount": 2,
        "baseLineCount": 4,
        "baseLineRemovedCount": 3
    },
    "issues": [
        {
            "file": "FILE_1",
            "line": 10,
            "type": "TYPE_1",
            "message": "MESSAGE_1"
        },
        {
            "file": "FILE_1",
            "line": 12,
            "type": "TYPE_2",
            "message": "MESSAGE_2"
        },
        {
            "file": "FILE_2",
            "line": 0,
            "type": "TYPE_1",
            "message": "MESSAGE_3"
        }
    ],
    "success": false
}
EOF;

        $this->assertIssuesOutput($expectedOuput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new JsonOutputFormatter();
    }
}
