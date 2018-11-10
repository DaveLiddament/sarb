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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParserLookupService;
use Webmozart\Assert\Assert;

class ResultsParsersRegistry implements ResultsParserLookupService
{
    /**
     * @var ResultsParser[]
     * @psalm-var array<string, ResultsParser>
     */
    private $resultsParsers;

    /**
     * resultsParsersRegistry constructor.
     *
     * @param ResultsParser[] $resultsParsers
     */
    public function __construct(iterable $resultsParsers)
    {
        $this->resultsParsers = [];
        foreach ($resultsParsers as $staticAnalysisResultsParser) {
            $this->addStaticAnalysisResultsParser($staticAnalysisResultsParser);
        }
    }

    /**
     * Returns a list of all ResultsParser codes.
     *
     * These are used to identify which Static Analysis tool is being used for generating a baseline or comparing
     * baseline results to.
     *
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return array_keys($this->resultsParsers);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultsParser(string $identifier): ResultsParser
    {
        if (array_key_exists($identifier, $this->resultsParsers)) {
            return $this->resultsParsers[$identifier];
        }

        $identifiers = array_map(function (ResultsParser $staticAnalysisResultsParser): Identifier {
            return $staticAnalysisResultsParser->getIdentifier();
        }, $this->resultsParsers);

        throw new InvalidResultsParserException($identifier, $identifiers);
    }

    /**
     * @return ResultsParser[]
     */
    public function getAll(): array
    {
        return $this->resultsParsers;
    }

    private function addStaticAnalysisResultsParser(ResultsParser $staticAnalysisResultsParser): void
    {
        $identifier = $staticAnalysisResultsParser->getIdentifier()->getCode();
        Assert::keyNotExists($this->resultsParsers, $identifier,
            "Multiple Static Analysis Results Parsers configured with the identifier [$identifier]"
        );

        $this->resultsParsers[$identifier] = $staticAnalysisResultsParser;
    }
}
