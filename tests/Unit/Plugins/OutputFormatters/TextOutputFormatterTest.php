<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TextOutputFormatter;

final class TextOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('text');
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
/FILE_1:10 - TYPE_1
MESSAGE_1

/FILE_1:12 - TYPE_2
MESSAGE_2

/FILE_2:0 - TYPE_1
MESSAGE_3


EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new TextOutputFormatter();
    }
}
