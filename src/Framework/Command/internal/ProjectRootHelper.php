<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class ProjectRootHelper
{
    private const PROJECT_ROOT = 'project-root';

    public static function configureProjectRootOption(Command $command): void
    {
        $command->addOption(
            self::PROJECT_ROOT,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the root of the project you are creating baseline for'
        );
    }

    /**
     * @throws SarbException
     */
    public static function getProjectRoot(InputInterface $input): ProjectRoot
    {
        $projectRootAsString = CliConfigReader::getOption($input, self::PROJECT_ROOT);
        $cwd = self::getCwd();

        if (null === $projectRootAsString) {
            $projectRootAsString = $cwd;
        }

        return new ProjectRoot($projectRootAsString, $cwd);
    }

    /**
     * @codeCoverageIgnore
     *
     * @throws SarbException
     */
    private static function getCwd(): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            throw new SarbException('Can not get current working directory. Specify project root with option: '.self::PROJECT_ROOT);
        }

        return $cwd;
    }
}
