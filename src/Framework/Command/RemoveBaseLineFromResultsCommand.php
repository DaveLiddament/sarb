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
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\BaseLineFileHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\CliConfigReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ErrorReporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\InvalidConfigException;
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

    /**
     * @var string
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

    public function __construct(
        ResultsPrunerInterface $resultsPruner,
        OutputFormatterLookupService $outputFormatterLookupService
    ) {
        $this->outputFormatterLookupService = $outputFormatterLookupService;
        parent::__construct(self::COMMAND_NAME);
        $this->resultsPruner = $resultsPruner;
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

            $prunedResults = $this->resultsPruner->getPrunedResults(
                $baseLineFileName,
                $inputAnalysisResultsAsString,
                $projectRoot
            );

            $outputAnalysisResults = $prunedResults->getPrunedResults();

            ErrorReporter::writeToStdError(
                $output,
                "Latest analysis issue count: {$prunedResults->getInputAnalysisResultsCount()}"
            );

            ErrorReporter::writeToStdError(
                $output,
                "Baseline issue count: {$prunedResults->getBaseLine()->getAnalysisResults()->getCount()}"
            );

            ErrorReporter::writeToStdError(
                $output,
                "Issues count with baseline removed: {$outputAnalysisResults->getCount()}"
            );

            $outputAsString = $outputFormatter->outputResults($outputAnalysisResults);
            $output->writeln($outputAsString);

            return $outputAnalysisResults->hasNoIssues() ? 0 : 1;
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
