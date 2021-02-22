<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\GithubActionsOutputFormatter;

class GithubActionsOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('github');
    }

    public function testNoIssues(): void
    {
        $this->assertNoIssuesOutput('');
    }

    public function testWithIssues(): void
    {
        $expectedOuput = <<<EOF
::TYPE_1 file=/FILE_1,line=10::MESSAGE_1
::TYPE_2 file=/FILE_1,line=12::MESSAGE_2
::TYPE_1 file=/FILE_2,line=0::MESSAGE_3
EOF;

        $this->assertIssuesOutput($expectedOuput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new GithubActionsOutputFormatter();
    }
}
