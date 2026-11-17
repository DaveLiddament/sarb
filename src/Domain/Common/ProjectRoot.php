<?php

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\Path;
use Webmozart\Assert\Assert;

/**
 * Holds the root directory for the project being analysed.
 *
 * It is recommended that all static analysis tools report the full path of the files they analyse.
 * The ResultsParses then use the getRelativePath method of the class and all file paths in the baseline are
 * stored as relative to the project root.
 *
 * NOTE: Assuming the GitHistoryAnalyser is being used then the root directory contains the .git directory.
 */
final class ProjectRoot implements \Stringable
{
    /**
     * @throws InvalidPathException
     */
    public static function fromCurrentWorkingDirectory(string $currentWorkingDirectory): self
    {
        Assert::true(Path::isAbsolute($currentWorkingDirectory));
        $rootDirectory = Path::canonicalize($currentWorkingDirectory);

        return new self($rootDirectory);
    }

    /**
     * @throws InvalidPathException
     */
    public static function fromProjectRoot(string $projectRoot, string $currentWorkingDirectory): self
    {
        if (Path::isAbsolute($projectRoot)) {
            $rootDirectory = Path::canonicalize($projectRoot);
        } else {
            $rootDirectory = Path::makeAbsolute($projectRoot, $currentWorkingDirectory);
        }

        return new self($rootDirectory);
    }

    private function __construct(
        private string $rootDirectory,
        private string $relativePath = '',
    ) {
    }

    /**
     * Return a new ProjectRoot configured with a relativePath.
     */
    public function withRelativePath(string $relativePath): self
    {
        return new self($this->rootDirectory, $relativePath);
    }

    /**
     * Converts a path relative to the directory holding the code being analysed into a path
     * relative to the project root.
     *
     * These are the same unless a relativePath is configured (the --relative-path-to-code option).
     */
    public function prependRelativePath(RelativeFileName $relativeFileName): RelativeFileName
    {
        if ('' === $this->relativePath) {
            return $relativeFileName;
        }

        return new RelativeFileName(Path::join([$this->relativePath, $relativeFileName->getFileName()]));
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
     *
     * @throws InvalidPathException
     */
    public function getAbsoluteFileName(RelativeFileName $relativeFileName): AbsoluteFileName
    {
        $absoluteFileName = Path::join([
            $this->rootDirectory,
            $this->relativePath,
            $relativeFileName->getFileName(),
        ]);

        try {
            return new AbsoluteFileName($absoluteFileName);
        } catch (InvalidPathException) {
            throw new \LogicException("Invalid $absoluteFileName");
        }
    }

    public function getProjectRootDirectory(): string
    {
        return $this->rootDirectory;
    }
}
