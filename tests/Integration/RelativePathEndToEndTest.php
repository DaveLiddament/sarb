<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\CreateBaseLineCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\RemoveBaseLineFromResultsCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * End to end test of using --relative-path-to-code with a results parser that supplies
 * relative paths. The git history tracking must map the tool relative file names on to
 * the project root relative names held in the git diff.
 */
final class RelativePathEndToEndTest extends TestCase
{
    use TestDirectoryTrait;

    private const RELATIVE_PATH_TO_CODE = 'code';
    private const ANALYSED_FILE = 'src/Widget.php';
    private const TYPE = 'SomeType';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var GitCliWrapper
     */
    private $gitWrapper;

    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
        $this->gitWrapper = new GitCliWrapper();
        $container = new Container();
        $this->application = $container->getApplication();
    }

    public function testHistoryTrackedForRelativePathResults(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);

        // Code lives in a sub directory of the project (git) root
        $analysedFile = self::RELATIVE_PATH_TO_CODE.'/'.self::ANALYSED_FILE;
        $this->writeFile($analysedFile, "line1\nline2\nline3\nline4\nline5\n");
        $this->gitWrapper->addAndCommit('Commit 1', $this->projectRoot);

        // Baseline an issue at line 3 (results are relative to the code directory)
        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            [
                '--input-format' => 'sarb-relative-json',
                '--relative-path-to-code' => self::RELATIVE_PATH_TO_CODE,
                '--project-root' => (string) $this->projectRoot,
                'baseline-file' => $this->getBaselineFilePath(),
            ],
            0,
            $this->createResults(3),
        );

        // Insert 2 lines above the baselined issue. The issue is now reported at line 5.
        $this->writeFile($analysedFile, "new1\nnew2\nline1\nline2\nline3\nline4\nline5\n");

        $output = $this->runCommand(
            RemoveBaseLineFromResultsCommand::COMMAND_NAME,
            [
                '--output-format' => 'json',
                '--relative-path-to-code' => self::RELATIVE_PATH_TO_CODE,
                '--project-root' => (string) $this->projectRoot,
                'baseline-file' => $this->getBaselineFilePath(),
            ],
            0,
            $this->createResults(5),
        );

        // The baselined issue must be recognised via the git history despite the line shift
        $this->assertStringContainsString('Issue count with baseline removed: 0', $output);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    private function createResults(int $lineNumber): string
    {
        $results = [
            [
                'line' => $lineNumber,
                'type' => self::TYPE,
                'message' => 'MESSAGE',
                'relative_path' => self::ANALYSED_FILE,
            ],
        ];

        $asString = json_encode($results);
        $this->assertNotFalse($asString);

        return $asString;
    }

    private function writeFile(string $relativeFileName, string $contents): void
    {
        $absoluteFileName = $this->projectRoot->getAbsoluteFileName(new RelativeFileName($relativeFileName));
        $this->fileSystem->dumpFile($absoluteFileName->getFileName(), $contents);
    }

    /**
     * @param array<string, string|null> $arguments
     */
    private function runCommand(
        string $commandName,
        array $arguments,
        int $expectedExitCode,
        string $stdin,
    ): string {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $arguments['command'] = $command->getName();

        $commandTester->setInputs([$stdin]);

        $actualExitCode = $commandTester->execute($arguments);
        $output = $commandTester->getDisplay();
        $this->assertSame($expectedExitCode, $actualExitCode, $output);

        return $output;
    }

    private function getBaselineFilePath(): string
    {
        return "{$this->projectRoot}/baseline.json";
    }
}
