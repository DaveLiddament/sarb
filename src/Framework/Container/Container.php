<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\HistoryAnalyser\HistoryFactory;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal\AddCommandCompilerPass;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal\AddHistoryFactoryCompilerPass;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal\AddOutputFormatterFactoryCompilerPass;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\internal\AddStaticAnalysisResultsParserCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class Container
{
    public const COMMAND_TAG = 'console.command';
    public const RESULTS_PARSER_TAG = 'resultsParser';
    public const HISTORY_FACTORY_TAG = 'historyFactory';
    public const OUTPUT_FORMATTER_TAG = 'outputFormatter';

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../../../config/'));
        $loader->load('services.yml');

        $containerBuilder->registerForAutoconfiguration(Command::class)->addTag(self::COMMAND_TAG);
        $containerBuilder->registerForAutoconfiguration(ResultsParser::class)->addTag(self::RESULTS_PARSER_TAG);
        $containerBuilder->registerForAutoconfiguration(HistoryFactory::class)->addTag(self::HISTORY_FACTORY_TAG);
        $containerBuilder->registerForAutoconfiguration(OutputFormatter::class)->addTag(self::OUTPUT_FORMATTER_TAG);
        $containerBuilder->addCompilerPass(new AddCommandCompilerPass());
        $containerBuilder->addCompilerPass(new AddStaticAnalysisResultsParserCompilerPass());
        $containerBuilder->addCompilerPass(new AddHistoryFactoryCompilerPass());
        $containerBuilder->addCompilerPass(new AddOutputFormatterFactoryCompilerPass());

        $containerBuilder->compile();
        $this->containerBuilder = $containerBuilder;
    }

    public function getApplication(): Application
    {
        /** @var Application $application */
        $application = $this->containerBuilder->get(Application::class);

        return $application;
    }
}
