<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Framework\Command;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\PrunedResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\RemoveBaseLineFromResultsCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\OutputFormatterRegistry;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitCommit;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TableOutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\BaseLineResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles\HistoryFactoryStub;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles\MockResultsPruner;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\TestDoubles\OutputFormatterStub;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

class RemoveBaseLineCommandTest extends TestCase
{
    private const INPUT_STRING_1 = <<<EOF
This is
a multiline
string
EOF;
    public const BASELINE_FILENAME = 'baseline1.sarb';
    public const BASELINE_FILE_ARGUMENT = 'baseline-file';
    public const OUTPUT_FORMAT_OPTION = '--output-format';
    public const PROJECT_ROOT = '--project-root';

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var OutputFormatterRegistry
     */
    private $outputFormatterRegistry;
    /**
     * @var OutputFormatter
     */
    private $defaultOutputFormater;
    /**
     * @var OutputFormatter
     */
    private $stubOutputFormatter;

    protected function setUp(): void
    {
        $this->defaultOutputFormater = new TableOutputFormatter();
        $this->stubOutputFormatter = new OutputFormatterStub();

        $this->outputFormatterRegistry = new OutputFormatterRegistry([
            $this->defaultOutputFormater,
            $this->stubOutputFormatter,
        ]);

        $this->projectRoot = new ProjectRoot('/tmp', '/tmp/foo/bar');
    }

    public function testNoNewIssues(): void
    {
        $commandTester = $this->createCommandTester(
            $this->defaultOutputFormater,
            $this->getAnalysisResultsWithXResults(0),
            self::BASELINE_FILENAME,
            null,
            null
        );

        $commandTester->execute([
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode(0, $commandTester);
        $this->assertResponseContains('Latest analysis issue count: 2', $commandTester);
        $this->assertResponseContains('Baseline issue count: 4', $commandTester);
        $this->assertResponseContains('Issues count with baseline removed: 0', $commandTester);
    }

    public function test1NewIssues(): void
    {
        $commandTester = $this->createCommandTester(
            $this->defaultOutputFormater,
            $this->getAnalysisResultsWithXResults(1),
            self::BASELINE_FILENAME,
            null,
            null
        );

        $commandTester->execute([
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode(1, $commandTester);
        $this->assertResponseContains('Issues count with baseline removed: 1', $commandTester);
    }

    public function testPickNonDefaultOutputFormatter(): void
    {
        $commandTester = $this->createCommandTester(
            $this->stubOutputFormatter,
            $this->getAnalysisResultsWithXResults(0),
            self::BASELINE_FILENAME,
            null,
            null
        );

        $commandTester->execute([
            self::OUTPUT_FORMAT_OPTION => OutputFormatterStub::CODE,
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode(0, $commandTester);
        $this->assertResponseContains(
            '[stub output formatter: Issues since baseline 0]',
            $commandTester
        );
    }

    public function testPickNonDefaultOutputFormatterWithIssues(): void
    {
        $commandTester = $this->createCommandTester(
            $this->stubOutputFormatter,
            $this->getAnalysisResultsWithXResults(8),
            self::BASELINE_FILENAME,
            null,
            null
        );

        $commandTester->execute([
            self::OUTPUT_FORMAT_OPTION => OutputFormatterStub::CODE,
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode(1, $commandTester);
        $this->assertResponseContains(
            '[stub output formatter: Issues since baseline 8]',
            $commandTester
        );
    }

    public function testInvalidResultsParser(): void
    {
        $commandTester = $this->createCommandTester(
            $this->defaultOutputFormater,
            $this->getAnalysisResultsWithXResults(0),
            self::BASELINE_FILENAME,
            null,
            null
        );

        $commandTester->execute([
            self::OUTPUT_FORMAT_OPTION => 'rubbish',
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode(2, $commandTester);
        $this->assertResponseContains(
            'Invalid value [rubbish] for option [output-format]. Pick one of: table|stub',
            $commandTester
        );
    }

    public function testSpecifyProjectRoot(): void
    {
        $commandTester = $this->createCommandTester(
            $this->defaultOutputFormater,
            $this->getAnalysisResultsWithXResults(0),
            self::BASELINE_FILENAME,
            $this->projectRoot,
            null
        );

        $commandTester->execute([
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
            self::PROJECT_ROOT => '/tmp',
        ]);

        $this->assertReturnCode(0, $commandTester);
    }

    /**
     * @psalm-return array<int,array{int,Throwable}>
     */
    public function exceptionDataProvider(): array
    {
        return [
            [4, new SarbException()],
            [5, new Exception()],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testException(int $expectedCode, Throwable $exception): void
    {
        $commandTester = $this->createCommandTester(
            $this->defaultOutputFormater,
            $this->getAnalysisResultsWithXResults(1),
            self::BASELINE_FILENAME,
            null,
            $exception
        );

        $commandTester->execute([
            self::BASELINE_FILE_ARGUMENT => self::BASELINE_FILENAME,
        ]);

        $this->assertReturnCode($expectedCode, $commandTester);
    }

    private function createCommandTester(
        OutputFormatter $expectedOutputFormatter,
        AnalysisResults $expectedAnalysisResults,
        string $baselineFileName,
        ?ProjectRoot $projectRoot,
        ?Throwable $exception
    ): CommandTester {
        $baseLineResultsBuilder = new BaseLineResultsBuilder();
        $baseLineResultsBuilder->add('file1', 1, 'type1');
        $baseLineResultsBuilder->add('file2', 2, 'type2');
        $baseLineResultsBuilder->add('file3', 3, 'type3');
        $baseLineResultsBuilder->add('file4', 4, 'type4');

        $baseLine = new BaseLine(
            new HistoryFactoryStub(),
            $baseLineResultsBuilder->build(),
            new SarbJsonResultsParser(),
            new GitCommit('fae40b3d596780ffd746dbd2300d05dcfbd09033')
        );

        $prunedResults = new PrunedResults(
            $baseLine,
            $expectedAnalysisResults,
            2
        );

        $mockResultsPruner = new MockResultsPruner(
            new BaseLineFileName($baselineFileName),
            self::INPUT_STRING_1,
            $prunedResults,
            $projectRoot,
            $exception
        );

        $command = new RemoveBaseLineFromResultsCommand(
            $mockResultsPruner,
            $this->outputFormatterRegistry
        );

        $commandTester = new CommandTester($command);
        $commandTester->setInputs([self::INPUT_STRING_1]);

        return $commandTester;
    }

    private function assertReturnCode(int $expectedReturnCode, CommandTester $commandTester): void
    {
        $this->assertSame($expectedReturnCode, $commandTester->getStatusCode(), $commandTester->getDisplay());
    }

    private function assertResponseContains(string $expectedMessage, CommandTester $commandTester): void
    {
        $output = $commandTester->getDisplay();
        $position = strpos($output, $expectedMessage);
        $this->assertNotFalse($position, "Can't find message [$expectedMessage] in [$output]");
    }

    private function getAnalysisResultsWithXResults(int $count): AnalysisResults
    {
        $projectRoot = new ProjectRoot('/', '/');

        $analysisResultsBuilder = new AnalysisResultsBuilder();
        for ($i = 0; $i < $count; ++$i) {
            $analysisResult = new AnalysisResult(
                Location::fromAbsoluteFileName(
                    new AbsoluteFileName("/FILE_$count"),
                    $projectRoot,
                    new LineNumber($count)
                ),
                new Type("TYPE_$i"),
                "MESSAGE_$i",
                'FULL_MESSAGE'
            );
            $analysisResultsBuilder->addAnalysisResult($analysisResult);
        }

        return $analysisResultsBuilder->build();
    }
}
