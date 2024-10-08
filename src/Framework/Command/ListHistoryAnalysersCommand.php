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

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\HistoryFactoryRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListHistoryAnalysersCommand extends Command
{
    public const COMMAND_NAME = 'list-history-analysers';

    /**
     * @var string|null
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * Constructor.
     */
    public function __construct(
        private HistoryFactoryRegistry $historyFactoryRegistry,
    ) {
        parent::__construct(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->historyFactoryRegistry->getIdentifiers() as $identifier) {
            $output->writeln($identifier);
        }

        return 0;
    }
}
