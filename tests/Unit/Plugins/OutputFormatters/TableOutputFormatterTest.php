<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TableOutputFormatter;

final class TableOutputFormatterTest extends AbstractOutputFormatterTestCase
{
    public function testName(): void
    {
        $this->assertName('table');
    }

    public function testNoIssues(): void
    {
        $expectedOutput = <<<EOF
No issues
EOF;

        $this->assertNoIssuesOutput($expectedOutput);
    }

    public function testWithIssues(): void
    {
        $expectedOutput = <<<EOF

FILE: /FILE_1
+------+--------+-------------+
| Line | Type   | Description |
+------+--------+-------------+
| 10   | TYPE_1 | MESSAGE_1   |
| 12   | TYPE_2 | MESSAGE_2   |
+------+--------+-------------+

FILE: /FILE_2
+------+--------+--------------------+
| Line | Type   | Description        |
+------+--------+--------------------+
| 0    | TYPE_1 | WARNING: MESSAGE_3 |
+------+--------+--------------------+

These results include warnings. To exclude warnings from output use the --ignore-warnings flag.

EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    public function testWithIssuesAndWarningsIgnored(): void
    {
        $expectedOutput = <<<EOF

FILE: /FILE_1
+------+--------+-------------+
| Line | Type   | Description |
+------+--------+-------------+
| 10   | TYPE_1 | MESSAGE_1   |
| 12   | TYPE_2 | MESSAGE_2   |
+------+--------+-------------+

EOF;

        $this->assertIssuesOutputWithWarningsIgnored($expectedOutput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new TableOutputFormatter();
    }
}
