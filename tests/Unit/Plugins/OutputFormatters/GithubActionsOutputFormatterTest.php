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
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\GithubActionsOutputFormatter;

final class GithubActionsOutputFormatterTest extends AbstractOutputFormatterTestCase
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
        $expectedOutput = <<<EOF
::error file=FILE_1,line=10::MESSAGE_1
::error file=FILE_1,line=12::MESSAGE_2
::warning file=FILE_2,line=0::MESSAGE_3
EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    public function testWorkflowCommandEscaping(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName('/path,to/FILE:1.php'),
            $projectRoot,
            new LineNumber(10),
        );
        $analysisResult = new AnalysisResult(
            $location,
            new Type('TYPE_1'),
            "50% of this message is broken\r\nSecond line",
            [],
            Severity::error(),
        );
        $analysisResultsBuilder = new AnalysisResultsBuilder();
        $analysisResultsBuilder->addAnalysisResult($analysisResult);

        // The GitHub Actions runner URL decodes %25/%0D/%0A in the message. Property values
        // (e.g. file) additionally need ':' and ',' escaping or they terminate the property list.
        $expectedOutput = '::error file=path%2Cto/FILE%3A1.php,line=10::50%25 of this message is broken%0D%0ASecond line';

        $this->assertSame($expectedOutput, $this->getOutputFormatter()->outputResults($analysisResultsBuilder->build()));
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new GithubActionsOutputFormatter();
    }
}
