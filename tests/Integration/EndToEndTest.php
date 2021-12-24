<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\Path;

// TODO this is getting a bit big. Split into multiple files.
class EndToEndTest extends TestCase
{
    use ResourceLoaderTrait;
    use TestDirectoryTrait;

    private const COMMIT_1_DIRECTORY = 'integration/commit1';
    private const COMMIT_1_RESULTS = 'commit1.json';

    private const COMMIT_2_DIRECTORY = 'integration/commit2';
    private const COMMIT_2_RESULTS = 'commit2.json';
    private const COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS = 'baseline-removed.json';

    private const COMMIT_3_DIRECTORY = 'integration/commit3';
    private const COMMIT_3_RESULTS = 'commit3.json';

    private const INVALID_RESULTS = 'invalid_analysis_results.json';

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

    public function testInvalidConfig(): void
    {
        $this->createTestDirectory();

        $arguments = [
            '--input-format' => 'rubbish',
            'baseline-file' => $this->getBaselineFilePath(),
        ];

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            11,
            self::COMMIT_1_RESULTS
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testInvalidAnalysisResults(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);
        $this->commit(self::COMMIT_1_DIRECTORY);

        $arguments = [
            'baseline-file' => $this->getBaselineFilePath(),
            '--project-root' => (string) $this->projectRoot,
        ];

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            13,
            self::INVALID_RESULTS
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testInvalidProjectRoot(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);
        $this->commit(self::COMMIT_1_DIRECTORY);

        $arguments = [
            'baseline-file' => $this->getProjectRootFilename('InvalidFileName.json'),
            '--project-root' => '/tmp/foo/bar',
        ];

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            15,
            self::COMMIT_1_RESULTS
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testInvalidBaselineFileNameSupplied(): void
    {
        $this->createTestDirectory();
        $arguments = [
            'baseline-file' => $this->getProjectRootFilename('InvalidFileName.json'),
        ];

        $this->runCommand(
            RemoveBaseLineFromResultsCommand::COMMAND_NAME,
            $arguments,
            14,
            self::COMMIT_1_RESULTS);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testInvalidBaselineContents(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);
        $this->commit(self::COMMIT_1_DIRECTORY);
        $arguments = [
            'baseline-file' => $this->getProjectRootFilename('src/Person.php'),
        ];

        $this->runCommand(
            RemoveBaseLineFromResultsCommand::COMMAND_NAME,
            $arguments,
            12,
            self::COMMIT_1_RESULTS);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testHappyPath(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);

        $this->commit(self::COMMIT_1_DIRECTORY);
        $this->runCreateBaseLineCommand();

        // Now create commit 2. THis introduces some new errors
        $this->commit(self::COMMIT_2_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_2_RESULTS,
            1,
            $this->getStaticAnalysisResultsAsString(self::COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS)
        );

        // Now create commit 3. This has errors that were only in the baseline.
        $this->commit(self::COMMIT_3_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_3_RESULTS,
            0,
        ''
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testRelativePathFlagHasNoAffectWhenUsingAbsolutPaths(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);

        $this->commit(self::COMMIT_1_DIRECTORY);
        $this->runCreateBaseLineCommand('code');

        // Now create commit 2. THis introduces some new errors
        $this->commit(self::COMMIT_2_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_2_RESULTS,
            1,
            $this->getStaticAnalysisResultsAsString(self::COMMIT_2_BASELINE_REMOVED_EXPECTED_RESULTS),
            'code'
        );

        // Now create commit 3. This has errors that were only in the baseline.
        $this->commit(self::COMMIT_3_DIRECTORY);
        $this->runStripBaseLineFromResultsCommand(
            self::COMMIT_3_RESULTS,
            0,
            ''
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testAttemptToCreateBaselineWithNonCleanGitStatus(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);
        $this->commit(self::COMMIT_1_DIRECTORY);
        $this->addNonCheckedInFile();

        $arguments = [
            'baseline-file' => $this->getProjectRootFilename('baseline.json'),
            '--project-root' => (string) $this->projectRoot,
        ];

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            15,
            self::COMMIT_1_RESULTS
        );

        $this->removeTestDirectory();
    }

    public function testForceCreateBaselineWithNonCleanGitStatus(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);
        $this->commit(self::COMMIT_1_DIRECTORY);
        $this->addNonCheckedInFile();

        $arguments = [
            'baseline-file' => $this->getProjectRootFilename('baseline.json'),
            '--project-root' => (string) $this->projectRoot,
            '--force' => null,
        ];

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            0,
            self::COMMIT_1_RESULTS
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    /**
     * This is just a smoke test.
     */
    public function testListSupportedStaticAnalysisTools(): void
    {
        $this->runCommand(ListResultsParsesCommand::COMMAND_NAME, [], 0, null);
    }

    /**
     * This is just a smoke test.
     */
    public function testListSupportedHistoryAnalysers(): void
    {
        $this->runCommand(ListHistoryAnalysersCommand::COMMAND_NAME, [], 0, null);
    }

    private function commit(string $directory): void
    {
        $source = $this->getPath($directory);
        $this->fileSystem->mirror($source, (string) $this->projectRoot, null, ['override' => true]);
        $this->updatePathsInJsonFiles($this->projectRoot);
        $this->gitWrapper->addAndCommit("Updating code to $directory", $this->projectRoot);
    }

    private function runCreateBaseLineCommand(?string $relativePathToCode = null): void
    {
        $arguments = [
            'baseline-file' => $this->getBaselineFilePath(),
            '--project-root' => (string) $this->projectRoot,
        ];

        if (null !== $relativePathToCode) {
            $arguments['--relative-path-to-code'] = $relativePathToCode;
        }

        $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            0,
            self::COMMIT_1_RESULTS
        );
    }

    private function runStripBaseLineFromResultsCommand(
        string $psalmResults,
        int $expectedExitCode,
        string $expectedResultsJson,
        ?string $relativePathToCode = null
    ): void {
        $arguments = [
            'baseline-file' => $this->getBaselineFilePath(),
            '--output-format' => 'json',
            '--project-root' => (string) $this->projectRoot,
        ];

        if (null !== $relativePathToCode) {
            $arguments['--relative-path-to-code'] = $relativePathToCode;
        }

        $output = $this->runCommand(
            RemoveBaseLineFromResultsCommand::COMMAND_NAME,
            $arguments,
            $expectedExitCode,
            $psalmResults
        );

        $output = str_replace('\/', '/', $output);

        $this->assertStringContainsString($expectedResultsJson, $output);
    }

    /**
     * @param array<string, string|null> $arguments
     */
    private function runCommand(
        string $commandName,
        array $arguments,
        int $expectedExitCode,
        ?string $resourceContainStdinContents
    ): string {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $arguments['command'] = $command->getName();

        if (null !== $resourceContainStdinContents) {
            $stdin = $this->getStaticAnalysisResultsAsString($resourceContainStdinContents);
            $commandTester->setInputs([$stdin]);
        }

        $actualExitCode = $commandTester->execute($arguments);
        $output = $commandTester->getDisplay();
        $this->assertEquals($expectedExitCode, $actualExitCode, $output);

        return $output;
    }

    private function getBaselineFilePath(): string
    {
        return "{$this->projectRoot}/baseline.json";
    }

    private function getStaticAnalysisResultsAsString(string $resourceName): string
    {
        $fileName = __DIR__.'/../resources/integration/staticAnalysisOutput/'.$resourceName;
        $rawResults = file_get_contents($fileName);
        $this->assertNotFalse($rawResults);
        $projectRootDirectory = (string) $this->projectRoot;
        $resultsWithPathsCorrected = str_replace('__SCRATCH_PAD_PATH__', $projectRootDirectory, $rawResults);

        return $resultsWithPathsCorrected;
    }

    private function getProjectRootFilename(string $resourceName): string
    {
        return $this->projectRoot->getAbsoluteFileName(new RelativeFileName($resourceName))->getFileName();
    }

    private function updatePathsInJsonFiles(ProjectRoot $projectRoot): void
    {
        $directory = $projectRoot->getProjectRootDirectory();

        $files = scandir($directory);
        $this->assertNotFalse($files);
        foreach ($files as $file) {
            if (StringUtils::endsWith('.json', $file)) {
                $fullPath = Path::makeAbsolute($file, $directory);
                $contents = file_get_contents($fullPath);
                $this->assertNotFalse($contents);
                $newContents = str_replace('__SCRATCH_PAD_PATH__', $directory, $contents);
                file_put_contents($fullPath, $newContents);
            }
        }
    }

    private function addNonCheckedInFile(): void
    {
        // Add a new file that is not checked in
        $newFile = new RelativeFileName('new.php');
        $this->fileSystem->dumpFile($this->projectRoot->getAbsoluteFileName($newFile)->getFileName(), 'new');
    }
}
