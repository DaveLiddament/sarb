<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class BaseLineFileHelper
{
    private const BASELINE_FILE = 'baseline-file';

    public static function configureBaseLineFileArgument(Command $command): void
    {
        $command->addArgument(
            self::BASELINE_FILE,
            InputArgument::REQUIRED, 'Baseline file'
        );
        $command->addOption(
            self::BASELINE_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the root of the project you are creating baseline for'
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getBaselineFile(InputInterface $input): FileName
    {
        return new FileName(CliConfigReader::getArgument($input, self::BASELINE_FILE));
    }
}
