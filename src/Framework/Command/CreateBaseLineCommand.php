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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner\BaseLineExporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLine;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactoryLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Importer;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\AbstractCommand;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\InvalidConfigException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\ResultsParsersRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBaseLineCommand extends AbstractCommand
{
    public const COMMAND_NAME = 'create-baseline';

    private const STATIC_ANALYSIS_TOOL = 'static-analysis-tool';
    private const DEFAULT_HISTORY_FACTORY_NAME = 'git';

    /**
     * @var string
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var BaseLineExporter
     */
    private $baseLineExporter;

    /**
     * @var Importer
     */
    private $resultsImporter;

    /**
     * @var ResultsParsersRegistry
     */
    private $resultsParsersRegistry;

    /**
     * @var HistoryFactoryLookupService
     */
    private $historyFactoryLookupService;

    /**
     * CreateBaseLineCommand constructor.
     *
     * @param ResultsParsersRegistry $resultsParsersRegistry
     * @param HistoryFactoryLookupService $historyFactoryLookupService
     * @param BaseLineExporter $exporter
     * @param Importer $resultsImporter
     */
    public function __construct(
        ResultsParsersRegistry $resultsParsersRegistry,
        HistoryFactoryLookupService $historyFactoryLookupService,
        BaseLineExporter $exporter,
        Importer $resultsImporter
    ) {
        $this->resultsParsersRegistry = $resultsParsersRegistry;
        $this->historyFactoryLookupService = $historyFactoryLookupService;
        $this->baseLineExporter = $exporter;
        $this->resultsImporter = $resultsImporter;
        parent::__construct(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureHook(): void
    {
        $this->setDescription('Creates a baseline of the static analysis results for the specified static analysis tool');

        $staticAnalysisParserIdentifiers = implode('|', $this->resultsParsersRegistry->getIdentifiers());

        $this->addArgument(
            self::STATIC_ANALYSIS_TOOL,
            InputArgument::REQUIRED,
            sprintf('Static analysis tool one of: %s', $staticAnalysisParserIdentifiers)
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
        $historyFactory = $this->historyFactoryLookupService->getHistoryFactory(self::DEFAULT_HISTORY_FACTORY_NAME);
        $historyMarker = $historyFactory->newHistoryMarkerFactory()->newCurrentHistoryMarker($projectRoot);
        $resultsParser = $this->getResultsParser($input);

        $analysisResults = $this->resultsImporter->importFromFile($resultsParser, $resultsFileName, $projectRoot);
        $baseLine = new BaseLine(
            $historyFactory,
            $analysisResults,
            $resultsParser,
            $historyMarker
        );
        $this->baseLineExporter->export($baseLine, $baseLineFileName);

        $errorsInBaseLine = count($baseLine->getAnalysisResults()->getAnalysisResults());
        $output->writeln('<info>Baseline created</info>');
        $output->writeln("<info>Errors in baseline $errorsInBaseLine</info>");

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @throws InvalidConfigException
     *
     * @return ResultsParser
     */
    private function getResultsParser(InputInterface $input): ResultsParser
    {
        $identifier = $this->getArgument($input, self::STATIC_ANALYSIS_TOOL);

        try {
            return $this->resultsParsersRegistry->getResultsParser($identifier);
        } catch (InvalidResultsParserException $e) {
            $validIdentifiers = array_map(function (Identifier $identifier): string {
                return $identifier->getCode();
            }, $e->getPossibleOptions());

            $message = 'Pick static analysis tool from one of: '.implode('|', $validIdentifiers);
            throw new InvalidConfigException(self::STATIC_ANALYSIS_TOOL, $message);
        }
    }
}
