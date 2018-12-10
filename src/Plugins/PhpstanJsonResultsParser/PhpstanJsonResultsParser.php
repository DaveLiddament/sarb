<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanJsonResultsParser;

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

    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    /**
     * PhpstanJsonResultsParser constructor.
     *
     * @param FqcnRemover $fqcnRemover
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
    public function convertToString(AnalysisResults $analysisResults): string
    {
        $files = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $location = $analysisResult->getLocation();
            $fileName = $location->getFileName();
            $fileNameAsString = $fileName->getFileName();

            if (!array_key_exists($fileNameAsString, $files)) {
                $files[$fileNameAsString] = [];
            }

            $files[$fileNameAsString][] = JsonUtils::toArray($analysisResult->getFullDetails());
        }

        $asArray = [
            'totals' => [
                'errors' => 0,
                'file_errors' => count($analysisResults->getAnalysisResults()),
            ],
            self::FILES => [],
            'errors' => [],
        ];

        foreach ($files as $fileName => $messages) {
            $asArray[self::FILES][$fileName] = [
                'errors' => count($messages),
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
     * @param array $analysisResultsAsArray
     * @param ProjectRoot $projectRoot
     *
     * @throws ParseAtLocationException
     *
     * @return AnalysisResults
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
                $fileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFilePath);
                $fileName = new FileName($fileNameAsString);

                $messages = ArrayUtils::getArrayValue($filesErrors, self::MESSAGES);

                foreach ($messages as $message) {
                    ArrayUtils::assertArray($message);
                    $analysisResult = $this->convertAnalysisResultFromArray($message, $fileName);
                    $analysisResults->addAnalysisResult($analysisResult);
                }
            } catch (ArrayParseException | JsonParseException | InvalidPathException $e) {
                throw new ParseAtLocationException("Result [$absoluteFilePath]", $e);
            }
        }

        return $analysisResults;
    }

    /**
     * @param array $analysisResultAsArray
     * @param FileName $fileName
     *
     * @throws ArrayParseException
     * @throws JsonParseException
     *
     * @return AnalysisResult
     */
    private function convertAnalysisResultFromArray(array $analysisResultAsArray, FileName $fileName): AnalysisResult
    {
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE);
        $rawType = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);
        $type = $this->fqcnRemover->removeRqcn($rawType);

        $location = new Location(
            $fileName,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($type),
            JsonUtils::toString($analysisResultAsArray)
        );
    }
}
