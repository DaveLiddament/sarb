<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpstanJsonResultsParser;

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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonParseException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

/**
 * Handles PHPStan's JSON output.
 *
 * NOTE: SARB only deals with errors that are attached to a particular line in a file.
 * PHPStan can report general errors (not specific to file). These are ignored by SARB.
 */
class PhpstanJsonResultsParser implements ResultsParser
{
    private const LINE = 'line';
    private const TYPE = 'message';
    private const FILES = 'files';
    private const MESSAGES = 'messages';
    private const MESSAGE = 'message';
    private const ABSOLUTE_FILE_PATH = 'absoluteFilePath';

    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    /**
     * PhpstanJsonResultsParser constructor.
     */
    public function __construct(FqcnRemover $fqcnRemover)
    {
        $this->fqcnRemover = $fqcnRemover;
    }

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
        return new PhpstanJsonIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function showTypeGuessingWarning(): bool
    {
        return true;
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
        $lineAsInt = ArrayUtils::getIntOrNullValue($analysisResultAsArray, self::LINE);

        // PHPStan sometimes reports errors not assigned to any line number. In this case give the line number as 0
        if (null === $lineAsInt) {
            $lineAsInt = 0;
        }

        $rawType = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);
        $type = $this->fqcnRemover->removeRqcn($rawType);

        $location = new Location(
            $fileName,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($type),
            $rawType,
            JsonUtils::toString([
                self::ABSOLUTE_FILE_PATH => $absoluteFilePath,
                self::MESSAGE => $analysisResultAsArray,
            ])
        );
    }
}
