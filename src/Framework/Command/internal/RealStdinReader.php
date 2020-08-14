<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;

/**
 * @codeCoverageIgnore
 */
class RealStdinReader implements StdinReader
{
    public function getStdin(): string
    {
        $string = stream_get_contents(STDIN);
        if (false === $string) {
            throw new SarbException('Can not read from STDIN');
        }

        return $string;
    }
}
