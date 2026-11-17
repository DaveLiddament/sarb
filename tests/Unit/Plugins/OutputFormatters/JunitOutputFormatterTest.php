<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\OutputFormatters;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\JunitOutputFormatter;

final class JunitOutputFormatterTest extends AbstractOutputFormatterTestCase
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

    public function testInvalidXmlCharactersAreRemoved(): void
    {
        // \x08 (backspace) is not valid in an XML 1.0 document. SimpleXML writes it to the
        // attribute unescaped, previously producing a document that could not be parsed
        // (the formatter then returned only an XML declaration).
        $output = $this->getOutputForMessage("MESSAGE\x08WITH\x08CONTROL_CHARS");

        $this->assertStringContainsString('message="MESSAGEWITHCONTROL_CHARS"', $output);
        $this->assertStringContainsString('<testsuite name="FILE_1"', $output);
    }

    public function testInvalidUtf8IsReducedToAscii(): void
    {
        // \xE9 is é in ISO-8859-1; it is not valid UTF-8
        $output = $this->getOutputForMessage("caf\xE9 message");

        $this->assertStringContainsString('message="caf message"', $output);
    }

    private function getOutputForMessage(string $message): string
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName('/FILE_1'),
            $projectRoot,
            new LineNumber(10),
        );
        $analysisResult = new AnalysisResult(
            $location,
            new Type('TYPE_1'),
            $message,
            [],
            Severity::error(),
        );
        $analysisResultsBuilder = new AnalysisResultsBuilder();
        $analysisResultsBuilder->addAnalysisResult($analysisResult);

        return $this->getOutputFormatter()->outputResults($analysisResultsBuilder->build());
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new JunitOutputFormatter();
    }
}
