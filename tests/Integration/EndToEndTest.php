<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\CreateBaseLineCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\ListHistoryAnalysersCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\ListResultsParsesCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\RemoveBaseLineFromResultsCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

// TODO this is getting a bit big. Split into multiple files.
class EndToEndTest extends TestCase
{
    use ResourceLoaderTrait;

    const COMMIT_1_DIRECTORY = 'integration/commit1';
    const COMMIT_1_PSALM_RESULTS = 'integration/psalmResults/commit1.json';

    const COMMIT_2_DIRECTORY = 'integration/commit2';
    const COMMIT_2_PSALM_RESULTS = 'integration/psalmResults/commit2.json';
    const COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS = 'integration/expectedResults/commit2-baseline-removed.json';

    const COMMIT_3_DIRECTORY = 'integration/commit3';
    const COMMIT_3_PSALM_RESULTS = 'integration/psalmResults/commit3.json';
    const COMMIT_3_BASELINE_REMOVED_EXPECTED_RESULTS = 'integration/expectedResults/commit3-baseline-removed.json';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var GitCliWrapper
     */
    private $gitWrapper;

    /**
     * @var string
     */
    private $testDirectory;

    /**
     * @var Application
     */
    private $application;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->fileSystem = new Filesystem();
        $this->gitWrapper = new GitCliWrapper();
        $container = new Container();
        $this->application = $container->getApplication();
        $this->testDirectory = '';
    }

    public function testInvalidConfig(): void
    {
        $arguments = [
            'static-analysis-tool' => 'rubbish',
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getPath(self::COMMIT_1_PSALM_RESULTS),
        ];

        $this->runCommand(CreateBaseLineCommand::COMMAND_NAME, $arguments, 2);
    }

    public function testInvalidBaselineSupplied(): void
    {
        $arguments = [
            'baseline-file' => $this->getPath(self::COMMIT_2_PSALM_RESULTS),
            'static-analysis-output-file' => $this->getPath(self::COMMIT_1_PSALM_RESULTS),
            'output-results-file' => $this->getSarbOutputFile('dummy.json'),
        ];

        $this->runCommand(RemoveBaseLineFromResultsCommand::COMMAND_NAME, $arguments, 3);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testHappyPath()
    {
        $this->createTestDirectory();
        $this->gitWrapper->init();

        $this->commit(self::COMMIT_1_DIRECTORY);
        $this->runCreateBaseLineCommand();

        // Now create commit 2. THis introduces some new errors
        $this->commit(self::COMMIT_2_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_2_PSALM_RESULTS,
            0,
            self::COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS,
            false
        );

        // Check exit code is correct when we set fail-on-analysis-results
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_2_PSALM_RESULTS,
            1,
            self::COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS,
            true
        );

        // Now create commit 3. This has errors that were only in the baseline.
        $this->commit(self::COMMIT_3_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_3_PSALM_RESULTS,
            0,
            self::COMMIT_3_BASELINE_REMOVED_EXPECTED_RESULTS,
            true
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    /**
     * This is just a smoke test.
     */
    public function testListSupportedStaticAnalysisTools(): void
    {
        $this->runCommand(ListResultsParsesCommand::COMMAND_NAME, [], 0);
    }

    /**
     * This is just a smoke test.
     */
    public function testListSupportedHistoryAnalysers(): void
    {
        $this->runCommand(ListHistoryAnalysersCommand::COMMAND_NAME, [], 0);
    }

    private function createTestDirectory(): void
    {
        $dateTimeFolderName = date('Ymd_His');
        $this->testDirectory = __DIR__."/../scratchpad/{$dateTimeFolderName}";
        $this->fileSystem->mkdir($this->testDirectory);
        $this->gitWrapper->setProjectRoot($this->testDirectory);
    }

    private function commit(string $directory): void
    {
        $source = $this->getPath($directory);
        $this->fileSystem->mirror($source, $this->testDirectory, null, ['override' => true]);
        $this->gitWrapper->addAndCommt("Updating code to $directory");
    }

    private function runCreateBaseLineCommand(): void
    {
        $arguments = [
            'static-analysis-tool' => 'psalm-json',
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getPath(self::COMMIT_1_PSALM_RESULTS),
            '--project-root' => $this->testDirectory,
        ];

        $this->runCommand(CreateBaseLineCommand::COMMAND_NAME, $arguments, 0);
    }

    private function runStripBaseLineFromResultsCommand(
        string $psalmResults,
        int $expectedExitCode,
        string $expectedResultsJson,
        bool $failureOnResultsAfterBaseline
    ): void {
        $outputResults = $this->getSarbOutputFile('output-after-baseline-removed.json');

        $arguments = [
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getPath($psalmResults),
            'output-results-file' => $outputResults,
            '--project-root' => $this->testDirectory,
        ];

        if ($failureOnResultsAfterBaseline) {
            $arguments['--failure-on-analysis-result'] = true;
        }

        $this->runCommand(RemoveBaseLineFromResultsCommand::COMMAND_NAME, $arguments, $expectedExitCode);

        // Now check both JSON are equal
        $actualResults = $this->loadJson($outputResults);
        $expectedResults = $this->loadJson($this->getPath($expectedResultsJson));
        $this->assertEquals($expectedResults, $actualResults);
    }

    private function runCommand(string $commandName, array $arguments, int $expectedExitCode): void
    {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $arguments['command'] = $command->getName();

        $actualExitCode = $commandTester->execute($arguments);
        $this->assertEquals($expectedExitCode, $actualExitCode, $commandTester->getDisplay());
    }

    private function getBaselineFilePath(): string
    {
        return $this->testDirectory.'/baseline.json';
    }

    private function loadJson(string $path): array
    {
        $jsonAsString = file_get_contents($path);

        return json_decode($jsonAsString, true);
    }

    private function removeTestDirectory(): void
    {
        $this->fileSystem->remove($this->testDirectory);
    }

    private function getSarbOutputFile(string $filename): string
    {
        return "{$this->testDirectory}/$filename";
    }
}
