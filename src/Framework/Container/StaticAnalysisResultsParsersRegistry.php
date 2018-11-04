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

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\StaticAnalysisResultsParser;
use Webmozart\Assert\Assert;

class StaticAnalysisResultsParsersRegistry
{
    /**
     * @var StaticAnalysisResultsParser[]
     * @psalm-var array<string, StaticAnalysisResultsParser>
     */
    private $staticAnalysisResultsParsers;

    /**
     * StaticAnalysisResultsParsersRegistry constructor.
     *
     * @param StaticAnalysisResultsParser[] $staticAnalysisResultsParsers
     */
    public function __construct(iterable $staticAnalysisResultsParsers)
    {
        $this->staticAnalysisResultsParsers = [];
        foreach ($staticAnalysisResultsParsers as $staticAnalysisResultsParser) {
            $this->addStaticAnalysisResultsParser($staticAnalysisResultsParser);
        }
    }

    /**
     * Returns a list of all StaticAnalysisResultsParser codes.
     *
     * These are used to identify which Static Analysis tool is being used for generating a baseline or comparing
     * baseline results to.
     *
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return array_keys($this->staticAnalysisResultsParsers);
    }

    /**
     * Returns StaticAnalysisResultsParser of the given name.
     *
     * @param string $identifier
     *
     * @return StaticAnalysisResultsParser
     */
    public function getStaticAnalysisResultsParser(string $identifier): StaticAnalysisResultsParser
    {
        Assert::keyExists($this->staticAnalysisResultsParsers, $identifier);

        return $this->staticAnalysisResultsParsers[$identifier];
    }

    /**
     * @return StaticAnalysisResultsParser[]
     */
    public function getAll(): array
    {
        return $this->staticAnalysisResultsParsers;
    }

    private function addStaticAnalysisResultsParser(StaticAnalysisResultsParser $staticAnalysisResultsParser): void
    {
        $identifier = $staticAnalysisResultsParser->getIdentifier()->getCode();
        Assert::keyNotExists($this->staticAnalysisResultsParsers, $identifier,
            "Multiple Static Analysis Results Parsers configured with the identifier [$identifier]"
        );

        $this->staticAnalysisResultsParsers[$identifier] = $staticAnalysisResultsParser;
    }
}
