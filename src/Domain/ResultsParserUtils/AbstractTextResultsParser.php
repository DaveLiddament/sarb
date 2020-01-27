<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParserUtils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

/**
 * Base class for any kind or ResultsParser that processes analysis results on a per line basis.
 */
abstract class AbstractTextResultsParser implements ResultsParser
{
    /**
     * @var string
     */
    private $regEx;

    /**
     * @var string
     */
    private $fileNamePosition;

    /**
     * @var string
     */
    private $lineNumberPosition;

    /**
     * @var string
     */
    private $typePosition;

    /**
     * @var string
     */
    private $messagePosition;

    /**
     * AbstractTextResultsParser constructor.
     */
    protected function __construct(
        string $regEx,
        string $fileNamePosition,
        string $lineNumberPosition,
        string $typePosition,
        string $messagePosition
    ) {
        $this->regEx = $regEx;
        $this->fileNamePosition = $fileNamePosition;
        $this->lineNumberPosition = $lineNumberPosition;
        $this->typePosition = $typePosition;
        $this->messagePosition = $messagePosition;
    }

    /**
     * {@inheritdoc}
     */
    final public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults
    {
        $analysisResults = new AnalysisResults();
        $lines = explode(PHP_EOL, $resultsAsString);

        $lineNumber = 0;
        foreach ($lines as $line) {
            ++$lineNumber;

            if (!StringUtils::isEmptyLine($line)) {
                try {
                    $analysisResult = $this->processLine($projectRoot, $line);
                } catch (SarbException $e) {
                    throw new ParseAtLocationException("Line [$lineNumber]", $e);
                }
                if (null !== $analysisResult) {
                    $analysisResults->addAnalysisResult($analysisResult);
                }
            }
        }

        return $analysisResults;
    }

    /**
     * {@inheritdoc}
     */
    final public function convertToString(AnalysisResults $analysisResults): string
    {
        $lines = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $lines[] = $analysisResult->getFullDetails();
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @throws SarbException
     */
    private function processLine(ProjectRoot $projectRoot, string $line): ?AnalysisResult
    {
        $matches = [];
        $isMatch = preg_match($this->regEx, $line, $matches);
        if (1 !== $isMatch) {
            throw new SarbException('Incorrect format');
        }

        if (!$this->includeLine($matches)) {
            return null;
        }

        $absoluteFileNameAsString = ArrayUtils::getStringValue($matches, $this->fileNamePosition);
        $lineAsInt = ArrayUtils::getIntAsStringValue($matches, $this->lineNumberPosition);
        $typeAsString = ArrayUtils::getStringValue($matches, $this->typePosition);
        $message = ArrayUtils::getStringValue($matches, $this->messagePosition);
        $relativeFileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFileNameAsString);

        $type = $this->getType($typeAsString);

        $location = new Location(
            new FileName($relativeFileNameAsString),
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($type),
            $message,
            $line
        );
    }

    abstract protected function getType(string $rawType): string;

    /**
     * Override if it is possible that you might not want to include this line in the results.
     *
     * @param string[] $matches
     */
    protected function includeLine(array $matches): bool
    {
        return true;
    }
}
