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

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
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
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\SeverityReader;

/**
 * Handles PHP Code Sniffers's JSON output.
 */
class PhpCodeSnifferJsonResultsParser implements ResultsParser
{
    private const LINE = 'line';
    private const SOURCE = 'source';
    private const FILES = 'files';
    private const SEVERITY = 'type';
    private const MESSAGES = 'messages';
    private const MESSAGE = 'message';

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
        $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, self::LINE);
        $rawMessage = ArrayUtils::getStringValue($analysisResultAsArray, self::MESSAGE);
        $rawSource = ArrayUtils::getStringValue($analysisResultAsArray, self::SOURCE);

        $location = Location::fromAbsoluteFileName(
            $absoluteFileName,
            $projectRoot,
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($rawSource),
            $rawMessage,
            $analysisResultAsArray,
            SeverityReader::getMandatorySeverity($analysisResultAsArray, self::SEVERITY)
        );
    }

    public function getIdentifier(): Identifier
    {
        return new PhpCodeSnifferJsonIdentifier();
    }

    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
}
