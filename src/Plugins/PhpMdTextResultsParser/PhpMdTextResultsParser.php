<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpMdTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParserUtils\AbstractTextResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;

class PhpMdTextResultsParser extends AbstractTextResultsParser
{
    private const LINE = '2';
    private const MESSAGE = '4';
    private const FILE = '1';
    private const REGEX = '/(.*):(\d+)(\s+)(.*)/';

    /** @var FqcnRemover */
    private $fqcnRemover;

    public function __construct(FqcnRemover $fqcnRemover)
    {
        parent::__construct(self::REGEX, self::FILE, self::LINE, self::MESSAGE, self::MESSAGE);
        $this->fqcnRemover = $fqcnRemover;
    }

    public function getIdentifier(): Identifier
    {
        return new PhpMdTextIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }

    protected function getType(string $rawType): string
    {
        return $this->fqcnRemover->removeRqcn($rawType);
    }
}
