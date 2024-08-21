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

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\ResultsParsersRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListResultsParsesCommand extends Command
{
    public const COMMAND_NAME = 'list-static-analysis-tools';

    /**
     * @var string|null
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * Constructor.
     */
    public function __construct(
        private ResultsParsersRegistry $staticAnalysisResultsParsersRegistry,
    ) {
        parent::__construct(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Supported static analysers');
        foreach ($this->staticAnalysisResultsParsersRegistry->getAll() as $resultsParser) {
            $identifier = $resultsParser->getIdentifier();

            $output->writeln(
                sprintf(
                    '[<info>%s</info>] %s',
                    $identifier->getCode(),
                    $identifier->getDescription(),
                ),
            );

            $output->writeln(
                sprintf(
                    '    Create baseline:         <info>%s | sarb create --input-format="%s"</info>',
                    $identifier->getToolCommand(),
                    $identifier->getCode(),
                ),
            );

            $output->writeln(
                sprintf(
                    '    Remove baseline results: <info>%s | sarb remove</info>',
                    $identifier->getToolCommand(),
                ),
            );
        }

        return 0;
    }
}
