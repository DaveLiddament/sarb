<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class BaseLineFileHelper
{
    private const BASELINE_FILE = 'baseline-file';

    public static function configureBaseLineFileArgument(Command $command): void
    {
        $command->addArgument(
            self::BASELINE_FILE,
            InputArgument::REQUIRED, 'Baseline file',
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public static function getBaselineFile(InputInterface $input): BaseLineFileName
    {
        return new BaseLineFileName(CliConfigReader::getArgument($input, self::BASELINE_FILE));
    }
}
