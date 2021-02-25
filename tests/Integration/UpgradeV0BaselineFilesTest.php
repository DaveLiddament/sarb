<?php

declare(strict_types=1);


namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Integration;


use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineExporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\UnifiedDiffParser\Parser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\UpgradeBaseLineCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\Container;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\HistoryFactoryRegistry;
use DaveLiddament\StaticAnalysisResultsBaseliner\Legacy\BaselineUpgrader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Legacy\LegacyResultsParserConverter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\GitDiffHistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\GitDiffHistoryAnalyser\internal\GitCliWrapper;
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
        $baseline = $this->projectRoot->getAbsoluteFileName(new RelativeFileName("baseline"));
        $this->baseLineFileName = new BaseLineFileName($baseline->getFileName());
        $this->fileWriter = new FileWriter();

        $container = new Container();
        $this->application = $container->getApplication();
        $this->upgradeCommand = $this->application->find(UpgradeBaseLineCommand::COMMAND_NAME);
    }

    public function testSarbUpgrade(): void
    {
        $originalFileContents = $this->getResource('v0/sarb.baseline');
        $this->fileWriter->writeFile($this->baseLineFileName, $originalFileContents);


        $commandTester = new CommandTester($this->upgradeCommand);
        $arguments = [
            'command' => $this->upgradeCommand->getName(),
            'baseline-file' => $this->baseLineFileName->getFileName(),
        ];

        $actualExitCode = $commandTester->execute($arguments);

        $this->assertSame(0, $actualExitCode);

    }


}
