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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;

class PsalmTextResultsParser extends AbstractTextResultsParser
{
    const REG_EX = '/(.*):(\d+):(\d+):(error|warning) - (.*)/';
    const LINE_FROM = '2';
    const TYPE = '5';
    const SEVERITY = '4';
    const FILE = '1';

    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    /**
     * PsalmTextResultsParser constructor.
     *
     * @param FqcnRemover $fqcnRemover
     */
    public function __construct(FqcnRemover $fqcnRemover)
    {
        parent::__construct(self::REG_EX, self::FILE, self::LINE_FROM, self::TYPE);
        $this->fqcnRemover = $fqcnRemover;
    }

    protected function getType(string $rawType): string
    {
        return $this->fqcnRemover->removeRqcn($rawType);
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
        return true;
    }

    protected function includeLine(array $matches): bool
    {
        return 'error' === $matches[self::SEVERITY];
    }
}
