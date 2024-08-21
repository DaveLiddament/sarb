<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

/**
 * Wrapper for rand.
 *
 * @final
 */
class RandomNumberGenerator
{
    public function getRandomNumber(int $maxNumber): int
    {
        return random_int(0, $maxNumber);
    }
}
