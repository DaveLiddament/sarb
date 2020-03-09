<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhanJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
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

    /**
     * {@inheritdoc}
     */
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        try {
            $asArray = JsonUtils::toArray($resultsAsString);
        } catch (JsonParseException $e) {
            throw new InvalidFileFormatException('Not a valid JSON format');
        }

        return $this->convertFromArray($asArray);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToString(AnalysisResults $analysisResults): string
    {
        $asArray = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $asArray[] = JsonUtils::toArray($analysisResult->getFullDetails());
        }

        return JsonUtils::toString($asArray);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): Identifier
    {
        return new PhanJsonIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function showTypeGuessingWarning(): bool
    {
        return false;
    }

    /**
     * Converts from an array.
     *
     * @phpstan-param array<mixed> $analysisResultsAsArray
     *
     * @throws ParseAtLocationException
     */
    private function convertFromArray(array $analysisResultsAsArray): AnalysisResults
    {
        $analysisResults = new AnalysisResults();

        $resultsCount = 0;

        /** @psalm-suppress MixedAssignment */
        foreach ($analysisResultsAsArray as $analysisResultAsArray) {
            ++$resultsCount;
            try {
                ArrayUtils::assertArray($analysisResultAsArray);
                $analysisResult = $this->convertAnalysisResultFromArray($analysisResultAsArray);
                $analysisResults->addAnalysisResult($analysisResult);
            } catch (ArrayParseException | JsonParseException | InvalidPathException $e) {
                throw new ParseAtLocationException("Result [$resultsCount]", $e);
            }
        }

        return $analysisResults;
    }

    /**
     * @phpstan-param array<mixed> $analysisResultAsArray
     *
     * @throws ArrayParseException
     * @throws JsonParseException
     */
    private function convertAnalysisResultFromArray(array $analysisResultAsArray): AnalysisResult
    {
        $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);
        $type = new Type($typeAsString);

        $message = ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE);

        $locationArray = ArrayUtils::getArrayValue($analysisResultAsArray, self::LOCATION);
        $fileNameAsString = ArrayUtils::getStringValue($locationArray, self::FILE_PATH);
        $fileName = new FileName($fileNameAsString);

        $linesArray = ArrayUtils::getArrayValue($locationArray, self::LINES);
        $lineAsInt = ArrayUtils::getIntValue($linesArray, self::LINE);

        $location = new Location(
            $fileName,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            $type,
            $message,
            JsonUtils::toString($analysisResultAsArray)
        );
    }
}
