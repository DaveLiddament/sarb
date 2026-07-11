<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\Path;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\CreateBaseLineCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\RemoveBaseLineFromResultsCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * End to end tests of the 4 combinations of baseline and analysis results, with and without
 * PHPStan error identifiers (which SARB uses as the violation type when supplied).
 */
final class PhpstanIdentifiersEndToEndTest extends TestCase
{
    use ResourceLoaderTrait;
    use TestDirectoryTrait;

    private const COMMIT_1_DIRECTORY = 'integration/commit1';

    private const RESULTS_WITH_IDENTIFIERS = 'phpstan-identifiers.json';
    private const RESULTS_WITH_IDENTIFIERS_REWORDED_PLUS_NEW_ISSUE = 'phpstan-identifiers-reworded-plus-new.json';
    private const RESULTS_WITH_IDENTIFIERS_PLUS_NEW_ISSUE = 'phpstan-identifiers-plus-new.json';
    private const RESULTS_WITHOUT_IDENTIFIERS = 'phpstan-no-identifiers.json';

    private const TYPES_FROM_TOOL_IDENTIFIERS_KEY = 'typesFromToolIdentifiers';
    private const TYPE_GUESSING_WARNING = 'guesses the classification of violations';
    private const REGENERATE_RECOMMENDATION = 'Regenerate the baseline';
    private const NEW_ISSUE_IDENTIFIER = 'method.notFound';

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

    public function testIdentifierBaseLineWithIdentifierResults(): void
    {
        $this->setUpProjectWithCommit1();

        $createOutput = $this->runCreateBaseLineCommand(self::RESULTS_WITH_IDENTIFIERS);
        $this->assertStringNotContainsString(self::TYPE_GUESSING_WARNING, $createOutput);
        $this->assertStringContainsString(
            '"'.self::TYPES_FROM_TOOL_IDENTIFIERS_KEY.'": "all"',
            $this->getBaseLineFileContents(),
        );

        // The messages have been reworded (as happens between PHPStan releases) but the
        // identifiers are unchanged, so only the genuinely new issue should be reported.
        $removeOutput = $this->runRemoveBaseLineCommand(self::RESULTS_WITH_IDENTIFIERS_REWORDED_PLUS_NEW_ISSUE, 1);
        $this->assertStringContainsString(self::NEW_ISSUE_IDENTIFIER, $removeOutput);
        $this->assertStringContainsString('Issue count with baseline removed: 1', $removeOutput);
        $this->assertStringNotContainsString('missingType.property', $removeOutput);
        $this->assertStringNotContainsString(self::REGENERATE_RECOMMENDATION, $removeOutput);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testLegacyBaseLineWithIdentifierResults(): void
    {
        $this->setUpProjectWithCommit1();

        $createOutput = $this->runCreateBaseLineCommand(self::RESULTS_WITHOUT_IDENTIFIERS);
        $this->assertStringContainsString(self::TYPE_GUESSING_WARNING, $createOutput);
        $this->assertStringNotContainsString(self::TYPES_FROM_TOOL_IDENTIFIERS_KEY, $this->getBaseLineFileContents());

        // The baseline holds guessed types. The results now contain identifiers, so the baselined
        // issues are matched via their legacy (guessed) types and the user is told to regenerate.
        $removeOutput = $this->runRemoveBaseLineCommand(self::RESULTS_WITH_IDENTIFIERS_PLUS_NEW_ISSUE, 1);
        $this->assertStringContainsString(self::REGENERATE_RECOMMENDATION, $removeOutput);
        $this->assertStringContainsString(self::NEW_ISSUE_IDENTIFIER, $removeOutput);
        $this->assertStringContainsString('Issue count with baseline removed: 1', $removeOutput);
        $this->assertStringNotContainsString('missingType.property', $removeOutput);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testIdentifierBaseLineWithResultsMissingIdentifiers(): void
    {
        $this->setUpProjectWithCommit1();

        $this->runCreateBaseLineCommand(self::RESULTS_WITH_IDENTIFIERS);

        // Baseline was built from identifiers, results contain none (e.g. an older PHPStan).
        // Nothing could be matched, so fail with a meaningful error.
        $removeOutput = $this->runRemoveBaseLineCommand(self::RESULTS_WITHOUT_IDENTIFIERS, 17);
        $this->assertStringContainsString(
            'The baseline was created from results that contained type identifiers',
            $removeOutput,
        );

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    public function testLegacyBaseLineWithLegacyResults(): void
    {
        $this->setUpProjectWithCommit1();

        $this->runCreateBaseLineCommand(self::RESULTS_WITHOUT_IDENTIFIERS);

        // Neither baseline nor results contain identifiers: exactly the behaviour of previous
        // SARB versions, with no new warnings.
        $removeOutput = $this->runRemoveBaseLineCommand(self::RESULTS_WITHOUT_IDENTIFIERS, 0);
        $this->assertStringContainsString('Issue count with baseline removed: 0', $removeOutput);
        $this->assertStringNotContainsString(self::REGENERATE_RECOMMENDATION, $removeOutput);

        // Only delete test directory if tests passed. Keep to investigate test failures
        $this->removeTestDirectory();
    }

    private function setUpProjectWithCommit1(): void
    {
        $this->createTestDirectory();
        $this->gitWrapper->init($this->projectRoot);

        $source = $this->getPath(self::COMMIT_1_DIRECTORY);
        $this->fileSystem->mirror($source, (string) $this->projectRoot, null, ['override' => true]);
        $this->updatePathsInJsonFiles($this->projectRoot);
        $this->gitWrapper->addAndCommit('Commit 1', $this->projectRoot);
    }

    private function runCreateBaseLineCommand(string $staticAnalysisOutputResource): string
    {
        $arguments = [
            '--input-format' => 'phpstan-json',
            '--project-root' => (string) $this->projectRoot,
            'baseline-file' => $this->getBaselineFilePath(),
        ];

        return $this->runCommand(
            CreateBaseLineCommand::COMMAND_NAME,
            $arguments,
            0,
            $staticAnalysisOutputResource,
        );
    }

    private function runRemoveBaseLineCommand(string $staticAnalysisOutputResource, int $expectedExitCode): string
    {
        $arguments = [
            '--output-format' => 'json',
            '--project-root' => (string) $this->projectRoot,
            'baseline-file' => $this->getBaselineFilePath(),
        ];

        return $this->runCommand(
            RemoveBaseLineFromResultsCommand::COMMAND_NAME,
            $arguments,
            $expectedExitCode,
            $staticAnalysisOutputResource,
        );
    }

    /**
     * @param array<string, string|null> $arguments
     */
    private function runCommand(
        string $commandName,
        array $arguments,
        int $expectedExitCode,
        string $staticAnalysisOutputResource,
    ): string {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $arguments['command'] = $command->getName();

        $commandTester->setInputs([$this->getStaticAnalysisResultsAsString($staticAnalysisOutputResource)]);

        $actualExitCode = $commandTester->execute($arguments);
        $output = $commandTester->getDisplay();
        $this->assertSame($expectedExitCode, $actualExitCode, $output);

        return $output;
    }

    private function getBaselineFilePath(): string
    {
        return "{$this->projectRoot}/baseline.json";
    }

    private function getBaseLineFileContents(): string
    {
        $contents = file_get_contents($this->getBaselineFilePath());
        $this->assertNotFalse($contents);

        return $contents;
    }

    private function getStaticAnalysisResultsAsString(string $resourceName): string
    {
        $fileName = __DIR__.'/../resources/integration/staticAnalysisOutput/'.$resourceName;
        $rawResults = file_get_contents($fileName);
        $this->assertNotFalse($rawResults);
        $projectRootDirectory = (string) $this->projectRoot;

        return str_replace('__SCRATCH_PAD_PATH__', $projectRootDirectory, $rawResults);
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
}
