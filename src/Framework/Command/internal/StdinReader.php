<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

interface StdinReader
{
    public function getStdin(): string;
}
