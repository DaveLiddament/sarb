<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
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

/**
 * Handles Phan's JSON output.
 */
class PhanJsonResultsParser implements ResultsParser
{
    private const LOCATION = 'location';
    private const LINES = 'lines';
    private const LINE = 'begin';
    private const TYPE = 'check_name';
    private const MESSAGE = 'description';
    private const FILE_PATH = 'path';

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
            } catch (ArrayParseException | InvalidPathException  $e) {
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
        $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);
        $type = new Type($typeAsString);

        $message = ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE);

        $locationArray = ArrayUtils::getArrayValue($analysisResultAsArray, self::LOCATION);

        $relativeFileNameAsString = ArrayUtils::getStringValue($locationArray, self::FILE_PATH);
        $relativeFileName = new RelativeFileName($relativeFileNameAsString);

        $linesArray = ArrayUtils::getArrayValue($locationArray, self::LINES);
        $lineAsInt = ArrayUtils::getIntValue($linesArray, self::LINE);

        $location = Location::fromRelativeFileName(
            $relativeFileName,
            $projectRoot,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            $type,
            $message,
            $analysisResultAsArray
        );
    }

    public function getIdentifier(): Identifier
    {
        return new PhanJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
}
