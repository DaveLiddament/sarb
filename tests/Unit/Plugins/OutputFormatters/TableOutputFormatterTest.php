<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TableOutputFormatter;

final class TableOutputFormatterTest extends AbstractOutputFormatterTest
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
        $expectedOuput = <<<EOF

FILE: /FILE_1
+------+-------------+
| Line | Description |
+------+-------------+
| 10   | MESSAGE_1   |
| 12   | MESSAGE_2   |
+------+-------------+

FILE: /FILE_2
+------+--------------------+
| Line | Description        |
+------+--------------------+
| 0    | WARNING: MESSAGE_3 |
+------+--------------------+

These results include warnings. To exclude warnings from output use the --ignore-warnings flag.

EOF;

        $this->assertIssuesOutput($expectedOuput);
    }

    public function testWithIssuesAndWarningsIgnored(): void
    {
        $expectedOuput = <<<EOF

FILE: /FILE_1
+------+-------------+
| Line | Description |
+------+-------------+
| 10   | MESSAGE_1   |
| 12   | MESSAGE_2   |
+------+-------------+

EOF;

        $this->assertIssuesOutputWithWarningsIgnored($expectedOuput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new TableOutputFormatter();
    }
}
