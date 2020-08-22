<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

class InvalidPathException extends SarbException
{
    public static function newInstance(string  $path, string $projectRootBasePath): self
    {
        $message = "Path [$path] not in the project root [$projectRootBasePath]. Is project root configured correctly?";

        return new self($message);
    }
}
