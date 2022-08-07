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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\InvalidOutputFormatterException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatterLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Pruner\ResultsPrunerInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\RandomResultsPicker\RandomResultsPicker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\BaseLineFileHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\CliConfigReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ErrorReporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\InvalidConfigException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\OutputWriter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ProjectRootHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\OutputFormatters\TableOutputFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class RemoveBaseLineFromResultsCommand extends Command
{
    public const COMMAND_NAME = 'remove-baseline-results';

    private const OUTPUT_FORMAT = 'output-format';
    private const SHOW_RANDOM_ERRORS = 'clean-up';

    /**
     * @var string|null
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var OutputFormatterLookupService
     */
    private $outputFormatterLookupService;
    /**
     * @var ResultsPrunerInterface
     */
    private $resultsPruner;
    /**
     * @var TableOutputFormatter
     */
    private $tableOutputFormatter;
    /**
     * @var RandomResultsPicker
     */
    private $randomResultsPicker;

    public function __construct(
        ResultsPrunerInterface $resultsPruner,
        OutputFormatterLookupService $outputFormatterLookupService,
        TableOutputFormatter $tableOutputFormatter,
        RandomResultsPicker $randomResultsPicker
    ) {
        $this->outputFormatterLookupService = $outputFormatterLookupService;
        parent::__construct(self::COMMAND_NAME);
        $this->resultsPruner = $resultsPruner;
        $this->tableOutputFormatter = $tableOutputFormatter;
        $this->randomResultsPicker = $randomResultsPicker;
    }

    protected function configure(): void
    {
        $this->setDescription('Shows issues created since the baseline');

        $outputFormatters = $this->outputFormatterLookupService->getIdentifiers();
        $this->addOption(
            self::OUTPUT_FORMAT,
            null,
            InputOption::VALUE_REQUIRED,
            'Output format. One of: '.implode('|', $outputFormatters),
            TableOutputFormatter::CODE
        );

        $this->addOption(
            self::SHOW_RANDOM_ERRORS,
            null,
            InputOption::VALUE_NONE,
            'Show a random 5 issues in the baseline to fix'
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
            $inputAnalysisResultsAsString = CliConfigReader::getStdin($input);
            $showRandomIssues = CliConfigReader::getBooleanOption($input, self::SHOW_RANDOM_ERRORS);

            $prunedResults = $this->resultsPruner->getPrunedResults(
                $baseLineFileName,
                $inputAnalysisResultsAsString,
                $projectRoot
            );

            $outputAnalysisResults = $prunedResults->getPrunedResults();

            OutputWriter::writeToStdError(
                $output,
                "Latest analysis issue count: {$prunedResults->getInputAnalysisResults()->getCount()}",
                false
            );

            OutputWriter::writeToStdError(
                $output,
                "Baseline issue count: {$prunedResults->getBaseLine()->getAnalysisResults()->getCount()}",
                false
            );

            OutputWriter::writeToStdError(
                $output,
                "Issue count with baseline removed: {$outputAnalysisResults->getCount()}",
                !$outputAnalysisResults->hasNoIssues()
            );

            $outputAsString = $outputFormatter->outputResults($outputAnalysisResults);
            $output->writeln($outputAsString);

            $returnCode = $outputAnalysisResults->hasNoIssues() ? 0 : 1;

            if ($showRandomIssues && !$prunedResults->getInputAnalysisResults()->hasNoIssues()) {
                $randomIssues = $this->randomResultsPicker->getRandomResultsToFix($prunedResults->getInputAnalysisResults());

                OutputWriter::writeToStdError(
                    $output,
                    "\n\nRandom {$randomIssues->getCount()} issues in the baseline to fix...",
                    false
                );

                $outputAsString = $this->tableOutputFormatter->outputResults($randomIssues);

                OutputWriter::writeToStdError(
                    $output,
                    $outputAsString,
                    false
                );
            }

            return $returnCode;
        } catch (Throwable $throwable) {
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
