<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpstanTextResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\FqcnRemover;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

class PhpstanTextResultsParser implements ResultsParser
{
    const LINE_FROM = '2';
    const TYPE = '3';
    const FILE = '1';

    /**
     * @var FqcnRemover
     */
    private $fqcnRemover;

    /**
     * PsalmTextResultsParser constructor.
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
                $analysisResults->addAnalysisResult($analysisResult);
            }
        }

        return $analysisResults;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToString(AnalysisResults $analysisResults): string
    {
        $lines = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $lines[] = $analysisResult->getFullDetails();
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): Identifier
    {
        return new PhpstanTextIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function showTypeGuessingWarning(): bool
    {
        return true;
    }

    /**
     * @param ProjectRoot $projectRoot
     * @param string $line
     *
     * @throws SarbException
     *
     * @return AnalysisResult
     */
    private function processLine(ProjectRoot $projectRoot, string $line): AnalysisResult
    {
        $matches = [];
        $isMatch = preg_match('/(.*):(\d+):(.*)/', $line, $matches);
        if (1 !== $isMatch) {
            throw new SarbException('Incorrect format');
        }
        $absoluteFileNameAsString = ArrayUtils::getStringValue($matches, self::FILE);
        $lineAsInt = ArrayUtils::getIntAsStringValue($matches, self::LINE_FROM);
        $typeAsString = ArrayUtils::getStringValue($matches, self::TYPE);
        $relativeFileNameAsString = $projectRoot->getPathRelativeToRootDirectory($absoluteFileNameAsString);

        $type = $this->fqcnRemover->removeRqcn($typeAsString);

        $location = new Location(
            new FileName($relativeFileNameAsString),
            new LineNumber($lineAsInt)
        );

        return new AnalysisResult(
            $location,
            new Type($type),
            $line
        );
    }
}
