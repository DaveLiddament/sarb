<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
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

/**
 * Handles PHPMD JSON output.
 */
class PhpmdJsonResultsParser implements ResultsParser
{
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
        /** @var array<string, array<mixed>> $files */
        $files = [];

        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $fullDetails = JsonUtils::toArray($analysisResult->getFullDetails());
            $violation = ArrayUtils::getArrayValue($fullDetails, 'violation');
            $absoluteFileName = ArrayUtils::getStringValue($fullDetails, 'fileName');

            if (!array_key_exists($absoluteFileName, $files)) {
                $files[$absoluteFileName] = [];
            }
            $files[$absoluteFileName][] = $violation;
        }

        $asArray = [
            'version' => '@project.version@',
            'package' => 'phpmd',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'files' => [],
        ];

        foreach ($files as $fileName => $violations) {
            $asArray['files'][] = [
                'file' => $fileName,
                'violations' => $violations,
            ];
        }

        return JsonUtils::toString($asArray);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): Identifier
    {
        return new PhpmdJsonIdentifier();
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
     * @throws InvalidFileFormatException
     */
    private function convertFromArray(array $analysisResultsAsArray, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResults = new AnalysisResults();

        try {
            $filesWithProblems = ArrayUtils::getArrayValue($analysisResultsAsArray, 'files');
        } catch (ArrayParseException $e) {
            throw new InvalidFileFormatException("Missing 'files' key at root level of JSON structure");
        }

        try {
            foreach ($filesWithProblems as $fileWithProblems) {
                ArrayUtils::assertArray($fileWithProblems);
                $absoluteFileName = ArrayUtils::getStringValue($fileWithProblems, 'file');
                $relativeFileName = $projectRoot->getPathRelativeToRootDirectory($absoluteFileName);
                $fileName = new FileName($relativeFileName);

                $violations = ArrayUtils::getArrayValue($fileWithProblems, 'violations');

                $this->processViolationsInFile($analysisResults, $absoluteFileName, $fileName, $violations);
            }
        } catch (ArrayParseException $e) {
            throw new InvalidFileFormatException("Invalid file format: {$e->getMessage()}");
        }

        return $analysisResults;
    }

    /**
     * @phpstan-param array<mixed> $violations
     *
     * @throws InvalidFileFormatException
     */
    private function processViolationsInFile(
        AnalysisResults $analysisResults,
        string $absoulteFileName,
        FileName $fileName,
        array $violations
    ): void {
        $violationCount = 1;
        foreach ($violations as $violation) {
            try {
                ArrayUtils::assertArray($violation);
                $analysisResult = $this->processViolation($absoulteFileName, $fileName, $violation);
                $analysisResults->addAnalysisResult($analysisResult);
                ++$violationCount;
            } catch (ArrayParseException | JsonParseException $e) {
                throw new InvalidFileFormatException("Can not process violation {$violationCount} for file {$fileName->getFileName()}");
            }
        }
    }

    /**
     * @phpstan-param array<mixed> $violation
     *
     * @throws ArrayParseException
     * @throws JsonParseException
     */
    private function processViolation(string $aboluteFileName, FileName $fileName, array $violation): AnalysisResult
    {
        $typeAsString = ArrayUtils::getStringValue($violation, 'rule');
        $type = new Type($typeAsString);

        $message = ArrayUtils::getStringValue($violation, 'description');

        $lineAsInt = ArrayUtils::getIntValue($violation, 'beginLine');

        $location = new Location(
            $fileName,
            new LineNumber($lineAsInt)
        );

        $detials = [
            'fileName' => $aboluteFileName,
            'violation' => $violation,
        ];

        return new AnalysisResult(
            $location,
            $type,
            $message,
            JsonUtils::toString($detials)
        );
    }
}
