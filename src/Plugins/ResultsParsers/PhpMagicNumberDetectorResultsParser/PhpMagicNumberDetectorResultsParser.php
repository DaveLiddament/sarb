<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpMagicNumberDetectorResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use Webmozart\Assert\Assert;

/**
 * Handles PHPMND CLI output.
 */
final class PhpMagicNumberDetectorResultsParser implements ResultsParser
{
    private const MAGIC_NUMBER_REGEX = "/^(.*):(\d+)\. Magic number: (.*)$/";

    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $lines = explode(\PHP_EOL, $resultsAsString);
        $analysisResultsBuilder = new AnalysisResultsBuilder();

        foreach ($lines as $line) {
            $matches = null;
            $match = preg_match(self::MAGIC_NUMBER_REGEX, $line, $matches);
            if (1 === $match) {
                Assert::count($matches, 4);
                $relativeFileName = ArrayUtils::getStringValue($matches, '1');
                $lineNumber = ArrayUtils::getIntAsStringValue($matches, '2');
                $magicNumber = ArrayUtils::getStringValue($matches, '3');

                $location = Location::fromRelativeFileName(
                    new RelativeFileName($relativeFileName),
                    $projectRoot,
                    new LineNumber($lineNumber),
                );

                $analysisResult = new AnalysisResult(
                    $location,
                    new Type($magicNumber),
                    "Magic number {$magicNumber}",
                    [],
                    Severity::error(),
                );

                $analysisResultsBuilder->addAnalysisResult($analysisResult);
            }
        }

        return $analysisResultsBuilder->build();
    }

    public function getIdentifier(): Identifier
    {
        return new PhpMagicNumberDetectorIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
}
