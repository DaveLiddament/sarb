<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\JsonOutputFormatter;

final class JsonOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('json');
    }

    public function testNoIssues(): void
    {
        $expectedOutput = <<<EOF
[]
EOF;

        $this->assertNoIssuesOutput($expectedOutput);
    }

    public function testWithIssues(): void
    {
        $expectedOutput = <<<EOF
[
    {
        "file": "\/FILE_1",
        "line": 10,
        "type": "TYPE_1",
        "message": "MESSAGE_1",
        "severity": "error"
    },
    {
        "file": "\/FILE_1",
        "line": 12,
        "type": "TYPE_2",
        "message": "MESSAGE_2",
        "severity": "error"
    },
    {
        "file": "\/FILE_2",
        "line": 0,
        "type": "TYPE_1",
        "message": "MESSAGE_3",
        "severity": "warning"
    }
]
EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new JsonOutputFormatter();
    }
}
