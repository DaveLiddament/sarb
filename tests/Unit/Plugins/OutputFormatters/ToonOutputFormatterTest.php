<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\ToonOutputFormatter;

final class ToonOutputFormatterTest extends AbstractOutputFormatterTestCase
{
    public function testName(): void
    {
        $this->assertName('toon');
    }

    public function testNoIssues(): void
    {
        $this->assertNoIssuesOutput('issues[0]{file,line,type,message,severity}:');
    }

    public function testWithIssues(): void
    {
        $expectedOutput = <<<'EOF'
        issues[3]{file,line,type,message,severity}:
          FILE_1,10,TYPE_1,MESSAGE_1,error
          FILE_1,12,TYPE_2,MESSAGE_2,error
          FILE_2,0,TYPE_1,MESSAGE_3,warning
        EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    public function testWithIssuesWithWarningsIgnored(): void
    {
        $expectedOutput = <<<'EOF'
        issues[2]{file,line,type,message,severity}:
          FILE_1,10,TYPE_1,MESSAGE_1,error
          FILE_1,12,TYPE_2,MESSAGE_2,error
        EOF;

        $this->assertIssuesOutputWithWarningsIgnored($expectedOutput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new ToonOutputFormatter();
    }
}
