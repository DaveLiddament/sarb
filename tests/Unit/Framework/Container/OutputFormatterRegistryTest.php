<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Framework\Container;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\InvalidOutputFormatterException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container\OutputFormatterRegistry;
use PHPUnit\Framework\TestCase;

final class OutputFormatterRegistryTest extends TestCase
{
    public function testListIdentifiers(): void
    {
        $outputFormatterRegistry = new OutputFormatterRegistry([
            new StubOutputFormatter1(),
            new StubOutputFormatter2(),
        ]);

        $actualIdentifiers = $outputFormatterRegistry->getIdentifiers();

        $this->assertSame([
            StubOutputFormatter1::OUTPUT_FORMATTER_NAME,
            StubOutputFormatter2::OUTPUT_FORMATTER_NAME,
        ], $actualIdentifiers);
    }

    public function testDuplicateName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OutputFormatterRegistry([
            new StubOutputFormatter1(),
            new StubOutputFormatter1(),
            new StubOutputFormatter2(),
        ]);
    }

    public function testGetOutputFormatter(): void
    {
        $outputFormatter = new StubOutputFormatter1();
        $outputFormatterRegistry = new OutputFormatterRegistry([
            $outputFormatter,
            new StubOutputFormatter2(),
        ]);

        $actualOutputFormatter = $outputFormatterRegistry->getOutputFormatter(StubOutputFormatter1::OUTPUT_FORMATTER_NAME);
        $this->assertSame($outputFormatter, $actualOutputFormatter);
    }

    public function testInvalidOutputFormatterName(): void
    {
        $outputFormatterRegistry = new OutputFormatterRegistry([
            new StubOutputFormatter1(),
            new StubOutputFormatter2(),
        ]);

        $this->expectException(InvalidOutputFormatterException::class);
        $outputFormatterRegistry->getOutputFormatter('rubbish');
    }
}
