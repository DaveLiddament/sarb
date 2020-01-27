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
     * @var string
     */
    protected static $defaultName = self::COMMAND_NAME;

    /**
     * @var ResultsParsersRegistry
     */
    private $staticAnalysisResultsParsersRegistry;

    /**
     * Constructor.
     */
    public function __construct(ResultsParsersRegistry $resultsParsersRegistry)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->staticAnalysisResultsParsersRegistry = $resultsParsersRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->staticAnalysisResultsParsersRegistry->getAll() as $resultsParser) {
            $identifier = $resultsParser->getIdentifier();
            $output->writeln(sprintf('[%s] %s', $identifier->getCode(), $identifier->getDescription()));
        }

        return 0;
    }
}
