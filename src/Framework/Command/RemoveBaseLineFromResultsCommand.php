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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\InvalidOutputFormatterException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatterLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\SummaryStats;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Importer;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\BaseLineFileHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\CliConfigReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ErrorReporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\InvalidConfigException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ProjectRootHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\StdinReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TableOutputFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveBaseLineFromResultsCommand extends Command
{
    public const COMMAND_NAME = 'remove-baseline-results';

    private const OUTPUT_FORMAT = 'output-format';

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
     * @var BaseLineImporter
     */
    private $baseLineImporter;
    /**
     * @var OutputFormatterLookupService
     */
    private $outputFormatterLookupService;
    /**
     * @var StdinReader
     */
    private $stdinReader;

    public function __construct(
        StdinReader $stdinReader,
        BaseLineResultsRemover $baseLineResultsRemover,
        BaseLineImporter $baseLineImporter,
        OutputFormatterLookupService $outputFormatterLookupService
    ) {
        $this->baseLineResultsRemover = $baseLineResultsRemover;
        $this->baseLineImporter = $baseLineImporter;
        $this->outputFormatterLookupService = $outputFormatterLookupService;
        $this->stdinReader = $stdinReader;
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Shows issues created since the baseline');

        $outputFormatters = $this->outputFormatterLookupService->getIdentifiers();
        $this->addArgument(
            self::OUTPUT_FORMAT,
            InputArgument::REQUIRED,
            'Output format. One of: '.implode('|', $outputFormatters),
            TableOutputFormatter::CODE
        );

        ProjectRootHelper::configureProjectRootOption($this);

        BaseLineFileHelper::configureBaseLineFileArgument($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $projectRoot = ProjectRootHelper::getProjectRoot($input);
            $outputFormatter = $this->getOutputFormatter($input);
            $baseLineFileName = BaseLineFileHelper::getBaselineFile($input);
            $input = $this->stdinReader->getStdin();

            $baseLine = $this->baseLineImporter->import($baseLineFileName);
            $resultsParser = $baseLine->getResultsParser();
            $historyFactory = $baseLine->getHistoryFactory();

            $historyAnalyser = $historyFactory->newHistoryAnalyser($baseLine->getHistoryMarker(), $projectRoot);
            $inputAnalysisResults = $resultsParser->convertFromString($input, $projectRoot);

            $outputAnalysisResults = $this->baseLineResultsRemover->pruneBaseLine(
                $inputAnalysisResults,
                $historyAnalyser,
                $baseLine->getAnalysisResults()
            );

            $summaryStats = new SummaryStats(
                $inputAnalysisResults->getCount(),
                $baseLine->getAnalysisResults()->getCount(),
                $resultsParser->getIdentifier(),
                $historyFactory->getIdentifier()
            );

            $outputAsString = $outputFormatter->outputResults($summaryStats, $outputAnalysisResults);

            $output->writeln($outputAsString);

            return $outputAnalysisResults->hasNoIssues() ? 0 : 1;
        } catch (\Throwable $throwable) {
            $returnCode = ErrorReporter::reportError($output, $throwable);

            return $returnCode;
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function getOutputFormatter(InputInterface $input): OutputFormatter
    {
        $identifier = CliConfigReader::getOptionWithDefaultValue($input, self::OUTPUT_FORMAT);

        try {
            return $this->outputFormatterLookupService->getOutputFormatter($identifier);
        } catch (InvalidOutputFormatterException $e) {
            throw InvalidConfigException::invalidOptionValue(self::OUTPUT_FORMAT, $identifier, $this->outputFormatterLookupService->getIdentifiers());
        }
    }
}
