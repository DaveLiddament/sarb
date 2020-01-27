<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParserUtils\AbstractTextResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;

class PhpstanTextResultsParser extends AbstractTextResultsParser
{
    private const LINE_FROM = '2';
    private const MESSAGE = '3';
    private const FILE = '1';
    private const REGEX = '/(.*):(\d+):(.*)/';

    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    /**
     * PsalmTextResultsParser constructor.
     */
    public function __construct(FqcnRemover $fqcnRemover)
    {
        parent::__construct(self::REGEX, self::FILE, self::LINE_FROM, self::MESSAGE, self::MESSAGE);
        $this->fqcnRemover = $fqcnRemover;
    }

    /**
     * {@inheritdoc}
     */
    protected function getType(string $rawType): string
    {
        return $this->fqcnRemover->removeRqcn($rawType);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): Identifier
    {
        return new PhpstanTextIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function showTypeGuessingWarning(): bool
    {
        return true;
    }
}
