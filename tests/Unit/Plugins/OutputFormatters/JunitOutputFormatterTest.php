<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\JunitOutputFormatter;

final class JunitOutputFormatterTest extends AbstractOutputFormatterTest
{
    public function testName(): void
    {
        $this->assertName('junit');
    }

    public function testNoIssues(): void
    {
        $expectedOutput = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites
        name="SARB" tests="1" failures="0">
    <testsuite errors="0" tests="1" failures="0" name="Success">
        <testcase name="Success"/>
    </testsuite>
</testsuites>

XML;

        $this->assertNoIssuesOutput($expectedOutput);
    }

    public function testWithIssues(): void
    {
        $expectedOutput = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites name="SARB" tests="3" failures="3">
  <testsuite name="FILE_1" errors="0" tests="2" failures="2">
    <testcase name="TYPE_1 at /FILE_1 (10:10)">
      <failure type="error" message="MESSAGE_1"/>
    </testcase>
    <testcase name="TYPE_2 at /FILE_1 (12:0)">
      <failure type="error" message="MESSAGE_2"/>
    </testcase>
  </testsuite>
  <testsuite name="FILE_2" errors="0" tests="1" failures="1">
    <testcase name="TYPE_1 at /FILE_2 (0:0)">
      <failure type="warning" message="MESSAGE_3"/>
    </testcase>
  </testsuite>
</testsuites>

XML;
        $this->assertIssuesOutput($expectedOutput);
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new JunitOutputFormatter();
    }
}
