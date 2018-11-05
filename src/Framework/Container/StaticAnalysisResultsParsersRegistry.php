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

use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\ResultsParserLookupService;
use DaveLiddament\StaticAnalysisResultsBaseliner\Core\ResultsParser\StaticAnalysisResultsParser;
use Webmozart\Assert\Assert;

class StaticAnalysisResultsParsersRegistry implements ResultsParserLookupService
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
     * {@inheritdoc}
     */
    public function getResultsParser(string $identifier): StaticAnalysisResultsParser
    {
        if (array_key_exists($identifier, $this->staticAnalysisResultsParsers)) {
            return $this->staticAnalysisResultsParsers[$identifier];
        }

        $identifiers = array_map(function (StaticAnalysisResultsParser $staticAnalysisResultsParser): Identifier {
            return $staticAnalysisResultsParser->getIdentifier();
        }, $this->staticAnalysisResultsParsers);

        throw new InvalidResultsParserException($identifier, $identifiers);
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
