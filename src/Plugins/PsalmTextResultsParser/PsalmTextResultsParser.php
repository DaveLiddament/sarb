<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParserUtils\AbstractTextResultsParser;

class PsalmTextResultsParser extends AbstractTextResultsParser
{
    const REG_EX = '/(.*):(\d+):(\d+):(error|warning) - (.*): (.*)/';
    const LINE_FROM = '2';
    const TYPE = '5';
    const SEVERITY = '4';
    const FILE = '1';

    public function __construct()
    {
        parent::__construct(self::REG_EX, self::FILE, self::LINE_FROM, self::TYPE);
    }

    protected function getType(string $rawType): string
    {
        return $rawType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): Identifier
    {
        return new PsalmTextIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function showTypeGuessingWarning(): bool
    {
        return false;
    }

    protected function includeLine(array $matches): bool
    {
        return 'error' === $matches[self::SEVERITY];
    }
}
