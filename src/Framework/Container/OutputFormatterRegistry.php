<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Container;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\InvalidOutputFormatterException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatter;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\OutputFormatter\OutputFormatterLookupService;
use Webmozart\Assert\Assert;

final class OutputFormatterRegistry implements OutputFormatterLookupService
{
    /**
     * @var array<string, OutputFormatter>
     */
    private array $outputFormatters;

    /**
     * OutputFormatterRegistry constructor.
     *
     * @param OutputFormatter[] $outputFormatters
     */
    public function __construct(array $outputFormatters)
    {
        $this->outputFormatters = [];
        foreach ($outputFormatters as $outputFormatter) {
            $this->addOutputFormatter($outputFormatter);
        }
    }

    /**
     * Returns a list of all OutputFormatter identifiers.
     *
     * These are used to identify which OutputFormatter to use.
     *
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return array_keys($this->outputFormatters);
    }

    public function getOutputFormatter(string $identifier): OutputFormatter
    {
        if (!array_key_exists($identifier, $this->outputFormatters)) {
            throw new InvalidOutputFormatterException($identifier);
        }

        return $this->outputFormatters[$identifier];
    }

    private function addOutputFormatter(OutputFormatter $outputFormatter): void
    {
        $identifier = $outputFormatter->getIdentifier();
        Assert::keyNotExists($this->outputFormatters, $identifier,
            "Multiple OutputFormatters configured with the identifier [$identifier]",
        );

        $this->outputFormatters[$identifier] = $outputFormatter;
    }
}
