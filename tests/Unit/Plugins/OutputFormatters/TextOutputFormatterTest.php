<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TextOutputFormatter;

class TextOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('text');
    }

    public function testNoIssues(): void
    {
        $expectedOutput = <<<EOF
Latest issue count: 2
Baseline issue count: 4
Issues count with baseline removed: 0
EOF;

        $this->assertNoIssuesOutput($expectedOutput);
    }

    public function testWithIssues(): void
    {
        $expectedOuput = <<<EOF
Latest issue count: 2
Baseline issue count: 4
Issues count with baseline removed: 3

--------

FILE_1:10 - TYPE_1
MESSAGE_1

FILE_1:12 - TYPE_2
MESSAGE_2

FILE_2:0 - TYPE_1
MESSAGE_3


EOF;

        $this->assertIssuesOutput($expectedOuput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new TextOutputFormatter();
    }
}
