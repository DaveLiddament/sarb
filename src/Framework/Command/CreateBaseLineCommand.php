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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Creator\BaseLineCreatorInterface;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactoryLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\InvalidHistoryFactoryException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParserLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\BaseLineFileHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\CliConfigReader;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ErrorReporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\InvalidConfigException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ProjectRootHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonIdentifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CreateBaseLineCommand extends Command
{
    public const COMMAND_NAME = 'create';

    public const INPUT_FORMAT = 'input-format';
    private const DEFAULT_STATIC_ANALYSIS_FORMAT = SarbJsonIdentifier::CODE;

    private const HISTORY_ANALYSER = 'history-analyser';
    private const DEFAULT_HISTORY_FACTORY_NAME = 'git';
    private const DOC_URL = 'https://github.com/DaveLiddament/sarb/blob/master/docs/ViolationTypeClassificationGuessing.md';

    /**
     * @var string|null
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var ResultsParserLookupService
     */
    private $resultsParserLookupService;

    /**
     * @var HistoryFactoryLookupService
     */
    private $historyFactoryLookupService;
    /**
     * @var BaseLineCreatorInterface
     */
    private $baseLineCreator;

    public function __construct(
        ResultsParserLookupService $resultsParsersLookupService,
        HistoryFactoryLookupService $historyFactoryLookupService,
        BaseLineCreatorInterface $baseLineCreator
    ) {
        $this->resultsParserLookupService = $resultsParsersLookupService;
        $this->historyFactoryLookupService = $historyFactoryLookupService;
        $this->baseLineCreator = $baseLineCreator;
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Creates a baseline of the static analysis results for the specified static analysis format');

        $staticAnalysisParserIdentifiers = implode('|', $this->resultsParserLookupService->getIdentifiers());
        $this->addOption(
            self::INPUT_FORMAT,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf('Static analysis tool. One of: %s', $staticAnalysisParserIdentifiers),
            self::DEFAULT_STATIC_ANALYSIS_FORMAT
        );

        $historyAnalyserIdentifiers = implode('|', $this->historyFactoryLookupService->getIdentifiers());
        $this->addOption(
            self::HISTORY_ANALYSER,
            null,
            InputOption::VALUE_REQUIRED,
            sprintf('History analyser. One of: %s', $historyAnalyserIdentifiers),
            self::DEFAULT_HISTORY_FACTORY_NAME
        );

        ProjectRootHelper::configureProjectRootOption($this);

        BaseLineFileHelper::configureBaseLineFileArgument($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $projectRoot = ProjectRootHelper::getProjectRoot($input);
            $historyFactory = $this->getHistoryFactory($input, $output);
            $resultsParser = $this->getResultsParser($input, $output);
            $baselineFile = BaseLineFileHelper::getBaselineFile($input);
            $analysisResultsAsString = CliConfigReader::getStdin($input);

            $baseLine = $this->baseLineCreator->createBaseLine(
                $historyFactory,
                $resultsParser,
                $baselineFile,
                $projectRoot,
                $analysisResultsAsString
            );

            $errorsInBaseLine = $baseLine->getAnalysisResults()->getCount();
            ErrorReporter::writeToStdError($output, '<info>Baseline created</info>');
            ErrorReporter::writeToStdError($output, "<info>Errors in baseline $errorsInBaseLine</info>");

            return 0;
        } catch (Throwable $throwable) {
            return ErrorReporter::reportError($output, $throwable);
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function getResultsParser(InputInterface $input, OutputInterface $output): ResultsParser
    {
        $identifier = CliConfigReader::getOptionWithDefaultValue($input, self::INPUT_FORMAT);

        try {
            $resultsParser = $this->resultsParserLookupService->getResultsParser($identifier);
        } catch (InvalidResultsParserException $e) {
            throw InvalidConfigException::invalidOptionValue(self::INPUT_FORMAT, $identifier, $this->resultsParserLookupService->getIdentifiers());
        }

        if ($resultsParser->showTypeGuessingWarning()) {
            $warning = '[%s] guesses the classification of violations. This means results might not be 100%% accurate. See %s for more details.';
            $output->writeln(sprintf($warning, $identifier, self::DOC_URL));
        }

        return $resultsParser;
    }

    /**
     * @throws InvalidConfigException
     */
    private function getHistoryFactory(InputInterface $input, OutputInterface $output): HistoryFactory
    {
        $identifier = CliConfigReader::getOptionWithDefaultValue($input, self::HISTORY_ANALYSER);

        try {
            $resultsParser = $this->historyFactoryLookupService->getHistoryFactory($identifier);
        } catch (InvalidHistoryFactoryException $e) {
            throw InvalidConfigException::invalidOptionValue(self::HISTORY_ANALYSER, $identifier, $this->historyFactoryLookupService->getIdentifiers());
        }

        return $resultsParser;
    }
}
