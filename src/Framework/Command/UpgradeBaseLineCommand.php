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

use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\BaseLineFileHelper;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal\ErrorReporter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Legacy\BaselineUpgrader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeBaseLineCommand extends Command
{
    public const COMMAND_NAME = 'upgrade-from-version-0';

    /**
     * @var string|null
     */
    protected static $defaultName = self::COMMAND_NAME;

    public function __construct(
        private BaselineUpgrader $baselineUpgrader,
    ) {
        parent::__construct(self::COMMAND_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Upgrades a baseline created by version 0.x of SARB to version 1');

        BaseLineFileHelper::configureBaseLineFileArgument($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $baselineFile = BaseLineFileHelper::getBaselineFile($input);
            $identifier = $this->baselineUpgrader->upgrade($baselineFile);
            $output->writeln('<info>Baseline updated.</info>');
            $output->writeln(
                sprintf(
                    "Update the command to remove baseline results to:\n%s | sarb remove %s",
                    $identifier->getToolCommand(),
                    $baselineFile->getFileName(),
                ),
            );

            return 0;
        } catch (\Throwable $throwable) {
            return ErrorReporter::reportError($output, $throwable);
        }
    }
}
