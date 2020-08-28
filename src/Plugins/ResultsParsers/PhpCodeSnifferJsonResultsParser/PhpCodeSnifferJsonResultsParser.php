<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpCodeSnifferJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

/**
 * Handles PHP Code Sniffers's JSON output.
 */
class PhpCodeSnifferJsonResultsParser implements ResultsParser
{
    private const LINE = 'line';
    private const SOURCE = 'source';
    private const FILES = 'files';
    private const MESSAGES = 'messages';
    private const MESSAGE = 'message';
    private const ABSOLUTE_FILE_PATH = 'absoluteFilePath';
    private const ERRORS = 'errors';
    private const WARNINGS = 'warnings';

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
    public function getIdentifier(): Identifier
    {
        return new PhpCodeSnifferJsonIdentifier();
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
    private function convertFromArray(array $analysisResultsAsArray, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResultsBuilder = new AnalysisResultsBuilder();

        try {
            $filesErrors = ArrayUtils::getArrayValue($analysisResultsAsArray, self::FILES);
        } catch (ArrayParseException $e) {
            throw ParseAtLocationException::issueParsing($e, 'Root node');
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($filesErrors as $absoluteFilePath => $fileErrors) {
            try {
                if (!is_string($absoluteFilePath)) {
                    throw new ArrayParseException('Expected filename to be of type string');
                }

                ArrayUtils::assertArray($fileErrors);

                $fileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFilePath);
                $fileName = new FileName($fileNameAsString);

                $messages = ArrayUtils::getArrayValue($fileErrors, self::MESSAGES);

                foreach ($messages as $message) {
                    ArrayUtils::assertArray($message);
                    $analysisResult = $this->convertAnalysisResultFromArray($message, $fileName, $absoluteFilePath);
                    $analysisResultsBuilder->addAnalysisResult($analysisResult);
                }
            } catch (ArrayParseException | JsonParseException | InvalidPathException $e) {
                throw ParseAtLocationException::issueParsing($e, "Result [$absoluteFilePath]");
            }
        }

        return $analysisResultsBuilder->build();
    }

    /**
     * @phpstan-param array<mixed> $analysisResultAsArray
     *
     * @throws ArrayParseException
     * @throws JsonParseException
     */
    private function convertAnalysisResultFromArray(
        array $analysisResultAsArray,
        FileName $fileName,
        string $absoluteFilePath
    ): AnalysisResult {
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE);
        $rawMessage = ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE);
        $rawSource = ArrayUtils::getStringValue($analysisResultAsArray, self::SOURCE);

        $location = new Location(
            $fileName,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($rawSource),
            $rawMessage,
            JsonUtils::toString([
                self::ABSOLUTE_FILE_PATH => $absoluteFilePath,
                self::MESSAGE => $analysisResultAsArray,
            ])
        );
    }
}
