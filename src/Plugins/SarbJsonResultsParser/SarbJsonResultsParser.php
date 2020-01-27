<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\SarbJsonResultsParser;

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

class SarbJsonResultsParser implements ResultsParser
{
    private const LINE = 'line';
    private const TYPE = 'type';
    private const FILE = 'file';
    private const MESSAGE = 'message';

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

        return $this->convertFromArray($asArray, $projectRoot);
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
        return new SarbJsonIdentifier();
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
     * @throws ParseAtLocationException
     */
    private function convertFromArray(array $analysisResultsAsArray, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResults = new AnalysisResults();

        $resultsCount = 0;

        /** @psalm-suppress MixedAssignment */
        foreach ($analysisResultsAsArray as $analysisResultAsArray) {
            ++$resultsCount;
            try {
                ArrayUtils::assertArray($analysisResultAsArray);
                $analysisResult = $this->convertAnalysisResultFromArray($analysisResultAsArray, $projectRoot);
                $analysisResults->addAnalysisResult($analysisResult);
            } catch (ArrayParseException | JsonParseException | InvalidPathException $e) {
                throw new ParseAtLocationException("Result [$resultsCount]", $e);
            }
        }

        return $analysisResults;
    }

    /**
     * @throws JsonParseException
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
        $relativeFileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFileNameAsString);

        $location = new Location(
            new FileName($relativeFileNameAsString),
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($typeAsString),
            ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE),
            JsonUtils::toString($analysisResultAsArray)
        );
    }
}
