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
    private const RELATIVE_PATH_TO_CODE = 'relative-path-to-code';

    public static function configureProjectRootOption(Command $command): void
    {
        $command->addOption(
            self::PROJECT_ROOT,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the root of the project you are creating baseline for',
        );

        $command->addOption(
            self::RELATIVE_PATH_TO_CODE,
            null,
            InputOption::VALUE_REQUIRED,
            "Relative path between project root and code being analysed. (Only needed for static analysis tools that don't provide full path to files containing issues)",
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
            return ProjectRoot::fromCurrentWorkingDirectory($cwd);
        }

        $projectRoot = ProjectRoot::fromProjectRoot($projectRootAsString, $cwd);

        $relativePathAsString = CliConfigReader::getOption($input, self::RELATIVE_PATH_TO_CODE);
        if (null !== $relativePathAsString) {
            $projectRoot = $projectRoot->withRelativePath($relativePathAsString);
        }

        return $projectRoot;
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
