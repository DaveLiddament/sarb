<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Legacy;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\InvalidResultsParserException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\ExakatJsonResultsParser\ExakatJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhanJsonResultsParser\PhanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser\PhpCodeSnifferJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser\PhpmdJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser\PhpstanJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser\PsalmJsonResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser\SarbJsonResultsParser;

class LegacyResultsParserConverter
{
    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    public function __construct(FqcnRemover $fqcnRemover)
    {
        $this->fqcnRemover = $fqcnRemover;
    }

    /** @throws InvalidResultsParserException */
    public function getNewResultsParser(string $legacyResultsParserIdentifier): ResultsParser
    {
        /** @var array<string,ResultsParser> */
        $lookup = [
            'exakat-sarb' => new ExakatJsonResultsParser(),
            'phan-json' => new PhanJsonResultsParser(),
            'phpcodesniffer-full' => new PhpCodeSnifferJsonResultsParser(),
            'phpcodesniffer-json' => new PhpCodeSnifferJsonResultsParser(),
            'phpmd-json' => new PhpmdJsonResultsParser(),
            'phpstan-json-tmp' => new PhpstanJsonResultsParser($this->fqcnRemover),
            'phpstan-text-tmp' => new PhpstanJsonResultsParser($this->fqcnRemover),
            'psalm-json' => new PsalmJsonResultsParser(),
            'psalm-text-tmp' => new PsalmJsonResultsParser(),
            'sarb-json' => new SarbJsonResultsParser(),
        ];

        $resultsParser = $lookup[$legacyResultsParserIdentifier] ?? null;

        if (null === $resultsParser) {
            throw InvalidResultsParserException::invalidIdentifier($legacyResultsParserIdentifier);
        }

        return $resultsParser;
    }
}
