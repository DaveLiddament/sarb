<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
use Webmozart\Assert\Assert;

/**
 * Holds the root directory for the project being analysed.
 *
 * It is recommended that all static analysis tools report the full path of the files they analyse.
 * The ResultsParses then use the getRelativePath method of the class and all file paths in the baseline are
 * stored as relative to the project root.
 *
 * NOTE: Assuming the GitHistoryAnalyser is being used then the root directory contains the .git directory.
 *
 *
 * TODO: provide support for relative paths (need to add current working directory to constructor)
 * TODO: make work with paths like /foo/bar/../baz
 * TODO: make work with windows
 */
class ProjectRoot
{
    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * ProjectRoot constructor.
     *
     * @param string $rootDirectory
     */
    public function __construct(string $rootDirectory)
    {
        // TODO remove assertions once proper support for paths is added.
        Assert::startsWith($rootDirectory, \DIRECTORY_SEPARATOR);
        Assert::false(strpos($rootDirectory, '..'));

        if (!StringUtils::endsWith(\DIRECTORY_SEPARATOR, $rootDirectory)) {
            $rootDirectory .= \DIRECTORY_SEPARATOR;
        }
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * Returns path relative to project root.
     *
     * @param string $fullPath
     *
     * @return string
     */
    public function getPathRelativeToRootDirectory(string $fullPath): string
    {
        Assert::startsWith($fullPath, $this->rootDirectory, "Path [$fullPath] not within project root [{$this->rootDirectory}]");

        return StringUtils::removeFromStart($this->rootDirectory, $fullPath);
    }
}
