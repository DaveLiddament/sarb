<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use PHPUnit\Framework\Assert;

trait AssertFileContentsSameTrait
{
    private function assertFileContentsSame(string $expected, string $actual): void
    {
        $cleanExpected = $this->clean($expected);
        $cleanActual = $this->clean($actual);

        Assert::assertEquals($cleanExpected, $cleanActual);
    }

    private function clean(string $dirty): string
    {
        $trimmed = trim($dirty);

        return str_replace('\\/', '/', $trimmed);
    }
}
