<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\BaseLiner;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\BaseLineFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use Exception;

/**
 * Used to for when importing a BaseLine file fails.
 */
class BaseLineImportException extends SarbException
{
    public static function fromException(BaseLineFileName $baseLineFile, Exception $e): self
    {
        $message = <<<EOF
Failed to import baseline file [{$baseLineFile->getFileName()}]. Is this a valid baseline file?.
{$e->getMessage()}
EOF;

        return new self($message, 0, $e);
    }
}
