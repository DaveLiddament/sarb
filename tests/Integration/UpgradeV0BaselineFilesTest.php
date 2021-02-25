<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\UpgradeBaseLineCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhanJsonResultsParser\PhanJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser\PhpCodeSnifferJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser\PhpstanJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser\PsalmJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonIdentifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers\ResourceLoaderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class UpgradeV0BaselineFilesTest extends TestCase
{
    use ResourceLoaderTrait;
    use TestDirectoryTrait;

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
     * @var BaseLineFileName
     */
    private $baseLineFileName;

    /**
     * @var FileWriter
     */
    private $fileWriter;

    /**
     * @var Application
     */
    private $application;
    /**
     * @var Command
     */
    private $upgradeCommand;

    protected function setUp(): void
    {
        $this->fileSystem = new Filesystem();
        $this->gitWrapper = new GitCliWrapper();
        $this->createTestDirectory();
        $baseline = $this->projectRoot->getAbsoluteFileName(new RelativeFileName('baseline'));
        $this->baseLineFileName = new BaseLineFileName($baseline->getFileName());
        $this->fileWriter = new FileWriter();

        $container = new Container();
        $this->application = $container->getApplication();
        $this->upgradeCommand = $this->application->find(UpgradeBaseLineCommand::COMMAND_NAME);
    }

    /** @return array<int,array{string,Identifier}> */
    public function dataProvider(): array
    {
        return [
            ['phan', new PhanJsonIdentifier()],
            ['phpcs-json', new PhpCodeSnifferJsonIdentifier()],
            ['phpcs-txt',  new PhpCodeSnifferJsonIdentifier()],
            ['phpmd', new PhpmdJsonIdentifier()],
            ['phpstan-json', new PhpstanJsonIdentifier()],
            ['phpstan-text', new PhpstanJsonIdentifier()],
            ['psalm-json', new PsalmJsonIdentifier()],
            ['psalm-text', new PsalmJsonIdentifier()],
            ['sarb', new SarbJsonIdentifier()],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUpgrade(string $file, Identifier $identifier): void
    {
        $this->createOriginalBaselineFile($file);

        $commandTester = new CommandTester($this->upgradeCommand);
        $arguments = [
            'command' => $this->upgradeCommand->getName(),
            'baseline-file' => $this->baseLineFileName->getFileName(),
        ];

        $actualExitCode = $commandTester->execute($arguments);

        // Check CLI responds with correct messaging to the user
        $this->assertSame(0, $actualExitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Baseline updated', $output);
        $this->assertStringContainsString($identifier->getToolCommand(), $output);

        // Check updated baseline is correct
        $fileReader = new FileReader();
        $updatedBaselineContents = $fileReader->readFile($this->baseLineFileName);
        $expectedBaselineContents = $this->getResource("v0/{$file}.expected");
        $this->assertSame($expectedBaselineContents, $updatedBaselineContents);

        $this->removeTestDirectory();
    }

    public function testUpgradeFails(): void
    {
        $this->createOriginalBaselineFile('invalid');

        $commandTester = new CommandTester($this->upgradeCommand);
        $arguments = [
            'command' => $this->upgradeCommand->getName(),
            'baseline-file' => $this->baseLineFileName->getFileName(),
        ];

        $actualExitCode = $commandTester->execute($arguments);

        $this->assertSame(12, $actualExitCode);
        $this->removeTestDirectory();
    }

    private function createOriginalBaselineFile(string $file): void
    {
        $originalBaselineContents = $this->getResource("v0/{$file}.baseline");
        $this->fileWriter->writeFile($this->baseLineFileName, $originalBaselineContents);
    }
}
