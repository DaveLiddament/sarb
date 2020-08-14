<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use Webmozart\PathUtil\Path;

/**
 * Holds the root directory for the project being analysed.
 *
 * It is recommended that all static analysis tools report the full path of the files they analyse.
 * The ResultsParses then use the getRelativePath method of the class and all file paths in the baseline are
 * stored as relative to the project root.
 *
 * NOTE: Assuming the GitHistoryAnalyser is being used then the root directory contains the .git directory.
 */
class ProjectRoot
{
    /**
     * @var string
     */
    private $rootDirectory;

    public function __construct(string $rootDirectory, string $currentWorkingDirectory)
    {
        if (Path::isAbsolute($rootDirectory)) {
            $this->rootDirectory = Path::canonicalize($rootDirectory);
        } else {
            $this->rootDirectory = Path::makeAbsolute($rootDirectory, $currentWorkingDirectory);
        }
    }

    /**
     * Returns path relative to project root.
     *
     * @throws InvalidPathException
     */
    public function getPathRelativeToRootDirectory(string $fullPath): string
    {
        if (!Path::isBasePath($this->rootDirectory, $fullPath)) {
            throw new InvalidPathException($fullPath, $this->rootDirectory);
        }

        return Path::makeRelative($fullPath, $this->rootDirectory);
    }

    public function __toString(): string
    {
        return $this->rootDirectory;
    }
}
