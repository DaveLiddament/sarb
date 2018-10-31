<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Framework\Command;

use DaveLiddament\StaticAnalysisBaseliner\Core\BaseLiner\BaseLineExporter;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\BaseLine;
use DaveLiddament\StaticAnalysisBaseliner\Core\Common\FileName;
use DaveLiddament\StaticAnalysisBaseliner\Core\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\Importer;
use DaveLiddament\StaticAnalysisBaseliner\Core\ResultsParser\StaticAnalysisResultsParser;
use DaveLiddament\StaticAnalysisBaseliner\Framework\Command\internal\AbstractCommand;
use DaveLiddament\StaticAnalysisBaseliner\Framework\Container\HistoryFactoryRegistry;
use DaveLiddament\StaticAnalysisBaseliner\Framework\Container\StaticAnalysisResultsParsersRegistry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBaseLineCommand extends AbstractCommand
{
    public const COMMAND_NAME = 'create-baseline';

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
     * CreateBaseLineCommand constructor.
     *
     * @param StaticAnalysisResultsParsersRegistry $staticAnalysisResultsParserRegistry
     * @param HistoryFactoryRegistry $historyFactoryRegistry
     * @param BaseLineExporter $exporter
     * @param Importer $resultsImporter
     */
    public function __construct(
        StaticAnalysisResultsParsersRegistry $staticAnalysisResultsParserRegistry,
        HistoryFactoryRegistry $historyFactoryRegistry,
        BaseLineExporter $exporter,
        Importer $resultsImporter
    ) {
        parent::__construct(self::COMMAND_NAME, $staticAnalysisResultsParserRegistry, $historyFactoryRegistry);
        $this->baseLineExporter = $exporter;
        $this->resultsImporter = $resultsImporter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureHook(): void
    {
        $this->setDescription('Creates a baseline of the static analysis results for the specified static analysis tool');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeHook(
        InputInterface $input,
        OutputInterface $output,
        StaticAnalysisResultsParser $staticAnalysisResultsParser,
        FileName $resultsFileName,
        FileName $baseLineFileName,
        HistoryFactory $historyFactory
    ): int {
        $analysisResults = $this->resultsImporter->importFromFile($staticAnalysisResultsParser, $resultsFileName);
        $baseLine = new BaseLine(
            $historyFactory->newHistoryMarkerFactory()->newCurrentHistoryMarker(),
            $analysisResults,
            $staticAnalysisResultsParser->getIdentifier()
        );
        $this->baseLineExporter->export($baseLine, $baseLineFileName);

        $errorsInBaseLine = count($baseLine->getAnalysisResults()->getAnalysisResults());
        $output->writeln('<info>Baseline created</info>');
        $output->writeln("<info>Errors in baseline $errorsInBaseLine</info>");

        return 0;
    }
}
