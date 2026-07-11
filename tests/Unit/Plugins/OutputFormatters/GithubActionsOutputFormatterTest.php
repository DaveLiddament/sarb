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
::error file=FILE_1,line=10,title=TYPE_1::MESSAGE_1
::error file=FILE_1,line=12,title=TYPE_2::MESSAGE_2
::warning file=FILE_2,line=0,title=TYPE_1::MESSAGE_3
EOF;

        $this->assertIssuesOutput($expectedOutput);
    }

    public function testTypeIsEscapedAsWorkflowCommandProperty(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');
        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName('/FILE_1'),
            $projectRoot,
            new LineNumber(10),
        );
        $analysisResult = new AnalysisResult(
            $location,
            new Type("Method foo, bar: 100% \r\n broken"),
            'MESSAGE_1',
            [],
            Severity::error(),
        );
        $analysisResultsBuilder = new AnalysisResultsBuilder();
        $analysisResultsBuilder->addAnalysisResult($analysisResult);

        $expectedOutput = '::error file=FILE_1,line=10,title=Method foo%2C bar%3A 100%25 %0D%0A broken::MESSAGE_1';

        $this->assertSame($expectedOutput, $this->getOutputFormatter()->outputResults($analysisResultsBuilder->build()));
    }

    protected function getOutputFormatter(): OutputFormatter
    {
        return new GithubActionsOutputFormatter();
    }
}
