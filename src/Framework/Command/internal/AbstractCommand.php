<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\File\FileImportException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\StaticAnalysisResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\HistoryFactoryRegistry;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\StaticAnalysisResultsParsersRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractCommand extends Command
{
    const STATIC_ANALYSIS_TOOL = 'static-analysis-tool';
    const RESULTS_FILE = 'static-analysis-output-file';
    const BASELINE_FILE = 'baseline-file';
    const PROJECT_ROOT = 'project-root';
    const DEFAULT_HISTORY_FACTORY_NAME = 'git';

    /**
     * @var StaticAnalysisResultsParsersRegistry
     */
    private $staticAnalysisResultsParsersRegistry;

    /**
     * @var HistoryFactoryRegistry
     */
    private $historyFactoryRegistry;

    /**
     * CreateBaseLineCommand constructor.
     *
     * @param string $commandName
     * @param StaticAnalysisResultsParsersRegistry $staticAnalysisResultsParserRegistry
     * @param HistoryFactoryRegistry $historyFactoryRegistry
     */
    public function __construct(
        string $commandName,
        StaticAnalysisResultsParsersRegistry $staticAnalysisResultsParserRegistry,
        HistoryFactoryRegistry $historyFactoryRegistry
    ) {
        $this->staticAnalysisResultsParsersRegistry = $staticAnalysisResultsParserRegistry;
        $this->historyFactoryRegistry = $historyFactoryRegistry;
        parent::__construct($commandName);
    }

    /**
     * {@inheritdoc}
     */
    final protected function configure(): void
    {
        $staticAnalysisParserIdentifiers = implode('|', $this->staticAnalysisResultsParsersRegistry->getIdentifiers());

        $this->addOption(
            self::PROJECT_ROOT,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the root of the project you are creating baseline for'
        );

        $this->addArgument(
            self::STATIC_ANALYSIS_TOOL,
            InputArgument::REQUIRED,
            sprintf('Static analysis tool one of: %s', $staticAnalysisParserIdentifiers)
        );

        $this->addArgument(
            self::RESULTS_FILE,
            InputArgument::REQUIRED,
            'Static analysis output file'
        );

        $this->addArgument(
            self::BASELINE_FILE,
            InputArgument::REQUIRED, 'Baseline file'
        );

        $this->configureHook();
    }

    abstract protected function configureHook(): void;

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $staticAnalysisResultsParser = $this->getStaticAnalyserResultsParser($input);
            $resultsFileName = $this->getFileName($input, self::RESULTS_FILE);
            $baseLineFileName = $this->getFileName($input, self::BASELINE_FILE);
            $historyFactory = $this->getHistoryFactory($input);

            return $this->executeHook(
                $input,
                $output,
                $staticAnalysisResultsParser,
                $resultsFileName,
                $baseLineFileName,
                $historyFactory
            );
        } catch (InvalidConfigException $e) {
            $output->writeln("<error>{$e->getProblem()}</error>");

            return 2;
        } catch (FileImportException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 3;
        } catch (SarbException $e) {
            $output->writeln("<error>Something went wrong: {$e->getMessage()}");

            return 4;
        } catch (Throwable $e) {
            // This should never happen. All exceptions should extend SarbException
            $output->writeln("<error>Unexpected critical error: {$e->getMessage()}</error>");

            return 5;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param StaticAnalysisResultsParser $staticAnalysisResultsParser
     * @param FileName $resultsFileName
     * @param FileName $baseLineFileName
     * @param HistoryFactory $historyFactory
     *
     * @throws SarbException
     * @throws InvalidConfigException
     * @throws FileImportException
     *
     * @return int
     */
    abstract protected function executeHook(
        InputInterface $input,
        OutputInterface $output,
        StaticAnalysisResultsParser $staticAnalysisResultsParser,
        FileName $resultsFileName,
        FileName $baseLineFileName,
        HistoryFactory $historyFactory
    ): int;

    /**
     * @param InputInterface $input
     *
     * @throws InvalidConfigException
     *
     * @return StaticAnalysisResultsParser
     */
    private function getStaticAnalyserResultsParser(InputInterface $input): StaticAnalysisResultsParser
    {
        $identifier = $this->getArgument($input, self::STATIC_ANALYSIS_TOOL);

        $validIdentifiers = $this->staticAnalysisResultsParsersRegistry->getIdentifiers();

        if (!in_array($identifier, $validIdentifiers, true)) {
            $message = 'Pick static analysis tool from one of: '.implode('|', $validIdentifiers);
            throw new InvalidConfigException(self::STATIC_ANALYSIS_TOOL, $message);
        }

        return $this->staticAnalysisResultsParsersRegistry->getStaticAnalysisResultsParser($identifier);
    }

    /**
     * @param InputInterface $input
     * @param string $argumentName
     *
     * @throws InvalidConfigException
     *
     * @return FileName
     */
    protected function getFileName(InputInterface $input, string $argumentName): FileName
    {
        return new FileName($this->getArgument($input, $argumentName));
    }

    /**
     * @param InputInterface $input
     *
     * @throws InvalidConfigException
     *
     * @return HistoryFactory
     */
    private function getHistoryFactory(InputInterface $input): HistoryFactory
    {
        // Only git supported now, so always use that
        $historyFactory = $this->historyFactoryRegistry->getHistoryFactory(self::DEFAULT_HISTORY_FACTORY_NAME);

        /**
         * @psalm-suppress MixedAssignment
         *
         * Lines directly after assignment check type of $projectRoot
         */
        $projectRoot = $input->getOption(self::PROJECT_ROOT);
        if (null !== $projectRoot) {
            if (!is_string($projectRoot)) {
                throw new InvalidConfigException(self::PROJECT_ROOT, 'Invalid value');
            }

            $historyFactory->setProjectRoot($projectRoot);
        }

        return $historyFactory;
    }

    /**
     * @param InputInterface $input
     * @param string $argumentName
     *
     * @throws InvalidConfigException
     *
     * @return string
     */
    protected function getArgument(InputInterface $input, string $argumentName): string
    {
        return $this->asString($input->getArgument($argumentName), $argumentName);
    }

    /**
     * @param InputInterface $input
     * @param string $optionName
     *
     * @throws InvalidConfigException
     *
     * @return string
     */
    protected function getOption(InputInterface $input, string $optionName): string
    {
        return $this->asString($input->getOption($optionName), $optionName);
    }

    /**
     * @param mixed $value
     * @param string $errorMessageContext
     *
     * @throws InvalidConfigException
     *
     * @return string
     */
    private function asString($value, string $errorMessageContext): string
    {
        if (is_string($value)) {
            return $value;
        }
        throw new InvalidConfigException($errorMessageContext, 'Invalid value');
    }
}
