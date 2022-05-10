<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

/**
 * Wrapper for rand.
 */
class RandomNumberGenerator
{
    public function getRandomNumber(int $maxNumber): int
    {
        return rand(0, $maxNumber);
    }
}
