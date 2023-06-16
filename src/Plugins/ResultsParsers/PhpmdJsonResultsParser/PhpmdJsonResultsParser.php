<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PhpmdJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
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
 * Handles PHPMD JSON output.
 */
class PhpmdJsonResultsParser implements ResultsParser
{
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResultsAsArray = JsonUtils::toArray($resultsAsString);
        $analysisResultsBuilder = new AnalysisResultsBuilder();

        try {
            $filesWithProblems = ArrayUtils::getArrayValue($analysisResultsAsArray, 'files');
        } catch (ArrayParseException $e) {
            throw ParseAtLocationException::issueParsingWithMessage("Missing 'files' key", 'root level of JSON structure');
        }

        $fileNumber = 0;
        try {
            /** @psalm-suppress MixedAssignment */
            foreach ($filesWithProblems as $fileWithProblems) {
                ++$fileNumber;
                ArrayUtils::assertArray($fileWithProblems);
                $absoluteFileNameAsString = ArrayUtils::getStringValue($fileWithProblems, 'file');
                $absoluteFileName = new AbsoluteFileName($absoluteFileNameAsString);

                $violations = ArrayUtils::getArrayValue($fileWithProblems, 'violations');

                $this->processViolationsInFile($analysisResultsBuilder, $absoluteFileName, $projectRoot, $violations);
            }
        } catch (ArrayParseException|InvalidPathException $e) {
            throw ParseAtLocationException::issueParsing($e, "Invalid file {$fileNumber}");
        }

        return $analysisResultsBuilder->build();
    }

    /**
     * @psalm-param array<mixed> $violations
     *
     * @throws ParseAtLocationException
     */
    private function processViolationsInFile(
        AnalysisResultsBuilder $analysisResultsBuilder,
        AbsoluteFileName $absoluteFileName,
        ProjectRoot $projectRoot,
        array $violations
    ): void {
        $violationCount = 1;
        /** @psalm-suppress MixedAssignment */
        foreach ($violations as $violation) {
            try {
                ArrayUtils::assertArray($violation);
                $analysisResult = $this->processViolation($absoluteFileName, $projectRoot, $violation);
                $analysisResultsBuilder->addAnalysisResult($analysisResult);
                ++$violationCount;
            } catch (ArrayParseException|InvalidPathException $e) {
                throw ParseAtLocationException::issueParsing($e, "File {$absoluteFileName->getFileName()}) violation {$violationCount}");
            }
        }
    }

    /**
     * @psalm-param array<mixed> $violation
     *
     * @throws ArrayParseException
     * @throws InvalidPathException
     */
    private function processViolation(
        AbsoluteFileName $aboluteFileName,
        ProjectRoot $projectRoot,
        array $violation
    ): AnalysisResult {
        $typeAsString = ArrayUtils::getStringValue($violation, 'rule');
        $type = new Type($typeAsString);

        $message = ArrayUtils::getStringValue($violation, 'description');

        $lineAsInt = ArrayUtils::getIntValue($violation, 'beginLine');

        $location = Location::fromAbsoluteFileName(
            $aboluteFileName,
            $projectRoot,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            $type,
            $message,
            $violation,
            Severity::error()
        );
    }

    public function getIdentifier(): Identifier
    {
        return new PhpmdJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
}
