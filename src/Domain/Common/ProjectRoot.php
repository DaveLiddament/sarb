<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use LogicException;
use Webmozart\Assert\Assert;
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

    public static function fromCurrentWorkingDirectory(string $currentWorkingDirectory): self
    {
        Assert::true(Path::isAbsolute($currentWorkingDirectory));
        $rootDirectory = Path::canonicalize($currentWorkingDirectory);

        return new self($rootDirectory);
    }

    public static function fromProjectRoot(string $projectRoot, string $currentWorkingDirectory): self
    {
        if (Path::isAbsolute($projectRoot)) {
            $rootDirectory = Path::canonicalize($projectRoot);
        } else {
            $rootDirectory = Path::makeAbsolute($projectRoot, $currentWorkingDirectory);
        }

        return new self($rootDirectory);
    }

    private function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * Returns path relative to project root.
     *
     * @throws InvalidPathException
     */
    public function getPathRelativeToRootDirectory(AbsoluteFileName $absoluteFileName): RelativeFileName
    {
        $fullPath = $absoluteFileName->getFileName();
        if (!Path::isBasePath($this->rootDirectory, $fullPath)) {
            throw InvalidPathException::notInProjectRoot($fullPath, $this->rootDirectory);
        }

        $relativeFileName = Path::makeRelative($fullPath, $this->rootDirectory);

        return new RelativeFileName($relativeFileName);
    }

    public function __toString(): string
    {
        return $this->getProjectRootDirectory();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getAbsoluteFileName(RelativeFileName $relativeFileName): AbsoluteFileName
    {
        $absoluteFileName = Path::join([$this->rootDirectory, $relativeFileName->getFileName()]);

        try {
            return new AbsoluteFileName($absoluteFileName);
        } catch (InvalidPathException $e) {
            throw new LogicException("Invalid $absoluteFileName");
        }
    }

    public function getProjectRootDirectory(): string
    {
        return $this->rootDirectory;
    }
}
