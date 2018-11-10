<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
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

class PsalmJsonResultsParser implements ResultsParser
{
    const LINE_FROM = 'line_from';
    const TYPE = 'type';
    const FILE = 'file_name';

    /**
     * {@inheritdoc}
     */
    public function convertFromString(string $resultsAsString): AnalysisResults
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
        return new PsalmJsonIdentifier();
    }

    /**
     * Converts from an array.
     *
     * @param array $analysisResultsAsArray
     *
     * @throws ParseAtLocationException
     *
     * @return AnalysisResults
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
            } catch (ArrayParseException | JsonParseException $e) {
                throw new ParseAtLocationException("Result [$resultsCount]", $e);
            }
        }

        return $analysisResults;
    }

    /**
     * @param array $analysisResultAsArray
     *
     * @throws ArrayParseException
     * @throws JsonParseException
     *
     * @return AnalysisResult
     */
    private function convertAnalysisResultFromArray(array $analysisResultAsArray): AnalysisResult
    {
        $fileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::FILE);
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE_FROM);
        $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);

        $location = new Location(
            new FileName($fileNameAsString),
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($typeAsString),
            JsonUtils::toString($analysisResultAsArray)
        );
    }
}
