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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
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

    public function __construct(FqcnRemover $fqcnRemover)
    {
        $this->fqcnRemover = $fqcnRemover;
    }

    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResultsAsArray = JsonUtils::toArray($resultsAsString);

        $analysisResultsBuilder = new AnalysisResultsBuilder();

        try {
            $filesErrors = ArrayUtils::getArrayValue($analysisResultsAsArray, self::FILES);
        } catch (ArrayParseException $e) {
            throw ParseAtLocationException::issueParsing($e, 'Root node');
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($filesErrors as $absoluteFileNameAsString => $fileErrors) {
            try {
                if (!is_string($absoluteFileNameAsString)) {
                    throw new ArrayParseException('Expected filename to be of type string');
                }

                ArrayUtils::assertArray($fileErrors);

                $absoluteFileName = new AbsoluteFileName($absoluteFileNameAsString);

                $messages = ArrayUtils::getArrayValue($fileErrors, self::MESSAGES);

                foreach ($messages as $message) {
                    ArrayUtils::assertArray($message);
                    $analysisResult = $this->convertAnalysisResultFromArray($message, $absoluteFileName, $projectRoot);
                    $analysisResultsBuilder->addAnalysisResult($analysisResult);
                }
            } catch (ArrayParseException | InvalidPathException $e) {
                throw ParseAtLocationException::issueParsing($e, "Result [$absoluteFileNameAsString]");
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
        AbsoluteFileName $absoluteFileName,
        ProjectRoot $projectRoot
    ): AnalysisResult {
        $lineAsInt = ArrayUtils::getIntOrNullValue($analysisResultAsArray, self::LINE);

        // PHPStan sometimes reports errors not assigned to any line number. In this case give the line number as 0
        if (null === $lineAsInt) {
            $lineAsInt = 0;
        }

        $rawType = ArrayUtils::getStringValue($analysisResultAsArray, self::TYPE);
        $type = $this->fqcnRemover->removeRqcn($rawType);

        $location = Location::fromAbsoluteFileName(
            $absoluteFileName,
            $projectRoot,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($type),
            $rawType,
            $analysisResultAsArray,
            Severity::error()
        );
    }

    public function getIdentifier(): Identifier
    {
        return new PhpstanJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return true;
    }
}
