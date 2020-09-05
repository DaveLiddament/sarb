<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

class PsalmJsonResultsParser implements ResultsParser
{
    const LINE_FROM = 'line_from';
    const TYPE = 'type';
    const FILE = 'file_path';
    const SEVERITY = 'severity';
    const ERROR_SEVERITY_LEVEL = 'error';
    const MESSAGE = 'message';

    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResultsAsArray = JsonUtils::toArray($resultsAsString);
        $analysisResultsBuilder = new AnalysisResultsBuilder();

        $resultsCount = 0;

        /** @psalm-suppress MixedAssignment */
        foreach ($analysisResultsAsArray as $analysisResultAsArray) {
            ++$resultsCount;
            try {
                ArrayUtils::assertArray($analysisResultAsArray);
                $severity = ArrayUtils::getStringValue($analysisResultAsArray, self::SEVERITY);
                if (self::ERROR_SEVERITY_LEVEL === $severity) {
                    $analysisResult = $this->convertAnalysisResultFromArray($analysisResultAsArray, $projectRoot);
                    $analysisResultsBuilder->addAnalysisResult($analysisResult);
                }
            } catch (ArrayParseException | InvalidPathException $e) {
                throw ParseAtLocationException::issueAtPosition($e, $resultsCount);
            }
        }

        return $analysisResultsBuilder->build();
    }

    /**
     * @psalm-param array<mixed> $analysisResultAsArray
     *
     * @throws ArrayParseException
     * @throws InvalidPathException
     */
    private function convertAnalysisResultFromArray(
        array $analysisResultAsArray,
        ProjectRoot $projectRoot
    ): AnalysisResult {
        $absoluteFileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::FILE);
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE_FROM);
        $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);

        $location = Location::fromAbsoluteFileName(
            new AbsoluteFileName($absoluteFileNameAsString),
            $projectRoot,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($typeAsString),
            ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE),
            $analysisResultAsArray
        );
    }

    public function getIdentifier(): Identifier
    {
        return new PsalmJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
}
