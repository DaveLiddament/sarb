<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

class LineDetector
{
    public static function isLine(string $input): bool
    {
        return StringUtils::startsWith('-------', $input);
    }
}
