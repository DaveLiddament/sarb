<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferJsonResultsParser;

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
     * @deprecated https://trello.com/c/Lj8VCsbY
     */
    public function convertToString(AnalysisResults $analysisResults): string
    {
        $files = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $fullDetails = JsonUtils::toArray($analysisResult->getFullDetails());
            $message = ArrayUtils::getArrayValue($fullDetails, self::MESSAGE);
            $absoluteFilePath = ArrayUtils::getStringValue($fullDetails, self::ABSOLUTE_FILE_PATH);

            if (!array_key_exists($absoluteFilePath, $files)) {
                $files[$absoluteFilePath] = [];
            }

            $files[$absoluteFilePath][] = $message;
        }

        $asArray = [
            'totals' => [
                'errors' => count(
                    array_filter(
                        $analysisResults->getAnalysisResults(),
                        static function (AnalysisResult $result): bool {
                            $details = JsonUtils::toArray($result->getFullDetails());
                            ArrayUtils::assertArray($details['message']);

                            return 'ERROR' === $details['message']['type'];
                        }
                    )
                ),
                'warnings' => count(
                    array_filter(
                        $analysisResults->getAnalysisResults(),
                        static function (AnalysisResult $result): bool {
                            $details = JsonUtils::toArray($result->getFullDetails());
                            ArrayUtils::assertArray($details['message']);

                            return 'WARNING' === $details['message']['type'];
                        }
                    )
                ),
                'fixable' => count(
                    array_filter(
                        $analysisResults->getAnalysisResults(),
                        static function (AnalysisResult $result): bool {
                            $details = JsonUtils::toArray($result->getFullDetails());
                            ArrayUtils::assertArray($details['message']);

                            return (bool) $details['message']['fixable'];
                        }
                    )
                ),
            ],
            self::FILES => [],
        ];

        foreach ($files as $fileName => $messages) {
            $asArray[self::FILES][$fileName] = [
                'errors' => count(
                    array_filter(
                        $messages,
                        static function (array $message): bool {
                            return 'ERROR' === $message['type'];
                        }
                    )
                ),
                'warnings' => count(
                    array_filter(
                        $messages,
                        static function (array $message): bool {
                            return 'WARNING' === $message['type'];
                        }
                    )
                ),
                self::MESSAGES => $messages,
            ];
        }

        return JsonUtils::toString($asArray);
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
        $analysisResults = new AnalysisResults();

        try {
            $filesErrors = ArrayUtils::getArrayValue($analysisResultsAsArray, self::FILES);
        } catch (ArrayParseException $e) {
            throw new ParseAtLocationException('Root node', $e);
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($filesErrors as $absoluteFilePath => $fileErrors) {
            try {
                if (!is_string($absoluteFilePath)) {
                    throw new ArrayParseException('Expected filename to be of type string');
                }

                ArrayUtils::assertArray($fileErrors);

                $errorsCount = ArrayUtils::getIntValue($fileErrors, self::ERRORS);
                $warningsCount = ArrayUtils::getIntValue($fileErrors, self::WARNINGS);
                if (0 === $errorsCount && 0 === $warningsCount) {
                    continue;
                }

                $fileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFilePath);
                $fileName = new FileName($fileNameAsString);

                $messages = ArrayUtils::getArrayValue($fileErrors, self::MESSAGES);

                foreach ($messages as $message) {
                    ArrayUtils::assertArray($message);
                    $analysisResult = $this->convertAnalysisResultFromArray($message, $fileName, $absoluteFilePath);
                    $analysisResults->addAnalysisResult($analysisResult);
                }
            } catch (ArrayParseException | JsonParseException | InvalidPathException $e) {
                throw new ParseAtLocationException("Result [$absoluteFilePath]", $e);
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
