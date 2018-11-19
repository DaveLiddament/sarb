<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
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
use Webmozart\PathUtil\Path;

// TODO this is getting a bit big. Split into multiple files.
class EndToEndTest extends TestCase
{
    use ResourceLoaderTrait;

    const COMMIT_1_DIRECTORY = 'integration/commit1';
    const COMMIT_1_PSALM_RESULTS = 'commit1.json';

    const COMMIT_2_DIRECTORY = 'integration/commit2';
    const COMMIT_2_PSALM_RESULTS = 'commit2.json';
    const COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS = 'expected-commit2-baseline-removed.json';

    const COMMIT_3_DIRECTORY = 'integration/commit3';
    const COMMIT_3_PSALM_RESULTS = 'commit3.json';
    const COMMIT_3_BASELINE_REMOVED_EXPECTED_RESULTS = 'expected-commit3-baseline-removed.json';

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

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->fileSystem = new Filesystem();
        $this->gitWrapper = new GitCliWrapper();
        $container = new Container();
        $this->application = $container->getApplication();
    }

    public function testInvalidConfig(): void
    {
        $this->createTestDirectory();
        $arguments = [
            'static-analysis-tool' => 'rubbish',
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getProjectRootFilename(self::COMMIT_1_PSALM_RESULTS),
        ];

        $this->runCommand(CreateBaseLineCommand::COMMAND_NAME, $arguments, 2);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testInvalidBaselineSupplied(): void
    {
        $this->createTestDirectory();
        $arguments = [
            'baseline-file' => $this->getProjectRootFilename(self::COMMIT_2_PSALM_RESULTS),
            'static-analysis-output-file' => $this->getProjectRootFilename(self::COMMIT_1_PSALM_RESULTS),
            'output-results-file' => $this->getProjectRootFilename('dummy.json'),
        ];

        $this->runCommand(RemoveBaseLineFromResultsCommand::COMMAND_NAME, $arguments, 3);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testHappyPath()
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);

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
        $testDirectory = __DIR__."/../scratchpad/{$dateTimeFolderName}";
        $this->fileSystem->mkdir($testDirectory);
        $this->projectRoot = new ProjectRoot($testDirectory, getcwd());
    }

    private function commit(string $directory): void
    {
        $source = $this->getPath($directory);
        $this->fileSystem->mirror($source, (string) $this->projectRoot, null, ['override' => true]);
        $this->updatePathsInJsonFiles((string) $this->projectRoot);
        $this->gitWrapper->addAndCommt("Updating code to $directory", $this->projectRoot);
    }

    private function runCreateBaseLineCommand(): void
    {
        $arguments = [
            'static-analysis-tool' => 'psalm-json',
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getProjectRootFilename(self::COMMIT_1_PSALM_RESULTS),
            '--project-root' => (string) $this->projectRoot,
        ];

        $this->runCommand(CreateBaseLineCommand::COMMAND_NAME, $arguments, 0);
    }

    private function runStripBaseLineFromResultsCommand(
        string $psalmResults,
        int $expectedExitCode,
        string $expectedResultsJson,
        bool $failureOnResultsAfterBaseline
    ): void {
        $outputResults = $this->getProjectRootFilename('output-after-baseline-removed.json');

        $arguments = [
            'baseline-file' => $this->getBaselineFilePath(),
            'static-analysis-output-file' => $this->getProjectRootFilename($psalmResults),
            'output-results-file' => $outputResults,
            '--project-root' => (string) $this->projectRoot,
        ];

        if ($failureOnResultsAfterBaseline) {
            $arguments['--failure-on-analysis-result'] = true;
        }

        $this->runCommand(RemoveBaseLineFromResultsCommand::COMMAND_NAME, $arguments, $expectedExitCode);

        // Now check both JSON are equal
        $actualResults = $this->loadJson($outputResults);
        $expectedResults = $this->loadJson($this->getProjectRootFilename($expectedResultsJson));
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
        return "{$this->projectRoot}/baseline.json";
    }

    private function loadJson(string $path): array
    {
        $jsonAsString = file_get_contents($path);

        return json_decode($jsonAsString, true);
    }

    private function removeTestDirectory(): void
    {
        $this->fileSystem->remove((string) $this->projectRoot);
    }

    private function getProjectRootFilename(string $filename): string
    {
        return Path::makeAbsolute($filename, (string) $this->projectRoot);
    }

    private function updatePathsInJsonFiles(string $directory): void
    {
        $files = scandir($directory);
        foreach ($files as $file) {
            if (StringUtils::endsWith('.json', $file)) {
                $fullPath = Path::makeAbsolute($file, $directory);
                $contents = file_get_contents($fullPath);
                $newContents = str_replace('__SCRATCH_PAD_PATH__', $directory, $contents);
                file_put_contents($fullPath, $newContents);
            }
        }
    }
}
