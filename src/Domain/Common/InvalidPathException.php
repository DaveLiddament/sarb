<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common;

class InvalidPathException extends SarbException
{
    public function __construct(string $path, string $basePath)
    {
        $message = "Path [$path] not in the project root [$basePath]. Is project root configured correctly?";
        parent::__construct($message);
    }
}
