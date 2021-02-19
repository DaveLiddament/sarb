<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\SarbJsonResultsParser;

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

class SarbJsonResultsParser implements ResultsParser
{
    private const LINE = 'line';
    private const TYPE = 'type';
    private const FILE = 'file';
    private const MESSAGE = 'message';

    public function getIdentifier(): Identifier
    {
        return new SarbJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }

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
                $analysisResult = $this->convertAnalysisResultFromArray($analysisResultAsArray, $projectRoot);
                $analysisResultsBuilder->addAnalysisResult($analysisResult);
            } catch (ArrayParseException | InvalidPathException $e) {
                throw ParseAtLocationException::issueAtPosition($e, $resultsCount);
            }
        }

        return $analysisResultsBuilder->build();
    }

    /**
     * @psalm-param array<mixed> $analysisResultAsArray
     *
     * @throws InvalidPathException
     * @throws ArrayParseException
     */
    private function convertAnalysisResultFromArray(
        array $analysisResultAsArray,
        ProjectRoot $projectRoot
    ): AnalysisResult {
        $absoluteFileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::FILE);
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE);
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
}
