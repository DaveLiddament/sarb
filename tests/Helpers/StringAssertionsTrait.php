<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use PHPUnit\Framework\Assert;

trait StringAssertionsTrait
{
    private function assertSameAllowingExtraNewLine(string $expectedValue, string $actualValue): void
    {
        // Depending on version of Command/CommandTester a PHP_EOL is added.
        // In this context and extra PHP_EOL make no difference. Benefit of allowing both is a greater range of
        // supported versions of Symfony/Command.
        $validResults = [
            $expectedValue,
            $expectedValue.\PHP_EOL,
        ];

        $isMatch = in_array($actualValue, $validResults, true);
        Assert::assertTrue($isMatch);
    }
}
