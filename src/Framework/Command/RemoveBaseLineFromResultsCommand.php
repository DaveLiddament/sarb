<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Analyser\BaseLineResultsRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineImporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Exporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Importer;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\AbstractCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class RemoveBaseLineFromResultsCommand extends AbstractCommand
{
    const COMMAND_NAME = 'remove-baseline-results';

    const OUTPUT_RESULTS_FILE = 'output-results-file';
    const FAILURE_ON_ANALYSIS_RESULT = 'failure-on-analysis-result';

    /**
     * @var string
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var BaseLineResultsRemover
     */
    private $baseLineResultsRemover;

    /**
     * @var Importer
     */
    private $resultsImporter;

    /**
     * @var Exporter
     */
    private $resultsExporter;

    /**
     * @var BaseLineImporter
     */
    private $baseLineImporter;

    /**
     * CreateBaseLineCommand constructor.
     *
     * @param BaseLineResultsRemover $baseLineResultsRemover
     * @param BaseLineImporter $baseLineImporter
     * @param Importer $resultsImporter
     * @param Exporter $resultsExporter
     */
    public function __construct(
        BaseLineResultsRemover $baseLineResultsRemover,
        BaseLineImporter $baseLineImporter,
        Importer $resultsImporter,
        Exporter $resultsExporter
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->baseLineResultsRemover = $baseLineResultsRemover;
        $this->baseLineImporter = $baseLineImporter;
        $this->resultsExporter = $resultsExporter;
        $this->resultsImporter = $resultsImporter;
    }

    protected function configureHook(): void
    {
        $this->setDescription('Creates a baseline of the static analysis results for the specified static analysis tool');

        $this->addArgument(
            self::OUTPUT_RESULTS_FILE,
            InputArgument::REQUIRED,
            'Output file (with baseline results removed)'
        );

        $this->addOption(
            self::FAILURE_ON_ANALYSIS_RESULT,
            'f',
            InputOption::VALUE_NONE,
            'Return error code if there any static analysis results after base line removed (useful for CI)'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function executeHook(
        InputInterface $input,
        OutputInterface $output,
        FileName $resultsFileName,
        FileName $baseLineFileName,
        ProjectRoot $projectRoot
    ): int {
        $outputResultsFile = $this->getFileName($input, self::OUTPUT_RESULTS_FILE);

        $baseLine = $this->baseLineImporter->import($baseLineFileName);
        $resultsParser = $baseLine->getResultsParser();
        $historyFactory = $baseLine->getHistoryFactory();

        $output->writeln(
            sprintf('<info>Baseline uses ResultsParser [%s] and HistoryAnalyser [%s]</info>',
                $resultsParser->getIdentifier()->getCode(),
                $historyFactory->getIdentifier())
        );

        $historyAnalyser = $historyFactory->newHistoryAnalyser($baseLine->getHistoryMarker(), $projectRoot);
        $baseLineAnalysisResults = $baseLine->getAnalysisResults();

        $inputAnalysisResults = $this->resultsImporter->importFromFile($resultsParser, $resultsFileName, $projectRoot);
        $outputAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
            $inputAnalysisResults,
            $historyAnalyser,
            $baseLineAnalysisResults
        );

        $this->resultsExporter->exportAnalysisResults(
            $outputAnalysisResults,
            $resultsParser,
            $outputResultsFile
        );

        $errorsAfterBaseLine = count($outputAnalysisResults->getAnalysisResults());
        $errorsBeforeBaseLine = count($inputAnalysisResults->getAnalysisResults());
        $errorsInBaseLine = count($baseLine->getAnalysisResults()->getAnalysisResults());

        $output->writeln("<info>Errors before baseline $errorsBeforeBaseLine</info>");
        $output->writeln("<info>Errors in baseline $errorsInBaseLine</info>");
        $output->writeln("<info>Errors introduced since baseline $errorsAfterBaseLine</info>");

        if ($errorsAfterBaseLine > 0) {
            $this->displayErrorsSinceBaseLine($output, $outputAnalysisResults->getOrderedAnalysisResults());

            if (true === $input->getOption(self::FAILURE_ON_ANALYSIS_RESULT)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param AnalysisResult[] $analysisResults
     */
    private function displayErrorsSinceBaseLine(OutputInterface $output, array $analysisResults): void
    {
        /** @var string[] $headings */
        $headings = [
            'Line',
            'Description',
        ];

        /** @var FileName $currentFileName */
        $currentFileName = null;
        /** @var Table|null $currentTable */
        $currentTable = null;
        foreach ($analysisResults as $analysisResult) {
            $fileName = $analysisResult->getLocation()->getFileName();

            if (!$fileName->isEqual($currentFileName)) {
                $this->renderTable($currentTable);

                $output->writeln("\nFILE: {$fileName->getFileName()}");
                $currentFileName = $fileName;
                $currentTable = new Table($output);
                $currentTable->setHeaders($headings);
            }

            Assert::notNull($currentTable);
            $currentTable->addRow([
                $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $analysisResult->getMessage(),
            ]);
        }

        $this->renderTable($currentTable);
    }

    private function renderTable(?Table $table): void
    {
        if (null !== $table) {
            $table->render();
        }
    }
}
