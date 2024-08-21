<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

final class InvalidPathException extends SarbException
{
    public static function notInProjectRoot(string $path, string $projectRootBasePath): self
    {
        $message = "Path [$path] not in the project root [$projectRootBasePath]. Is project root configured correctly?";

        return new self($message);
    }

    public static function notAbsolutePath(string $path): self
    {
        return new self("[$path] is not absolute");
    }

    public static function operatingSystemNotSupported(): self
    {
        return new self('Your environment or operating system is not supported.');
    }
}
