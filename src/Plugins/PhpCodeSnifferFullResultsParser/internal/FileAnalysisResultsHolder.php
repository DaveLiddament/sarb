<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ArrayUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\PhpCodeSnifferFullResultsParser;
use Webmozart\Assert\Assert;

/**
 * Holds AnalysisResults for a single file.
 *
 * Used for displaying output (e.g. for finding out max column widths, etc)
 */
class FileAnalysisResultsHolder
{
    /**
     * @var AnalysisResult[]
     */
    private $analysisResults;

    /**
     * @var FileName
     */
    private $fileName;

    /**
     * @var int
     */
    private $maxLineNumberColumnWidth;

    /**
     * @var int
     */
    private $maxSeverityColumnWidth;

    /**
     * @var int
     */
    private $fixableIssues;

    /**
     * @var int
     */
    private $maxMessageWidth;

    /**
     * @var int
     */
    private $numberOfErrors;

    /**
     * @var int
     */
    private $numberOfWarnings;

    /**
     * @var int[]
     */
    private $lineNumbersAffected;

    /**
     * @var string|null
     */
    private $absoluteFileNameAsString;

    public function __construct(FileName $fileName)
    {
        $this->fileName = $fileName;
        $this->analysisResults = [];
        $this->maxLineNumberColumnWidth = 0;
        $this->maxSeverityColumnWidth = 0;
        $this->fixableIssues = 0;
        $this->maxMessageWidth = 0;
        $this->numberOfWarnings = 0;
        $this->numberOfErrors = 0;
        $this->lineNumbersAffected = [];
        $this->absoluteFileNameAsString = null;
    }

    public function addAnalysisResult(AnalysisResult $analysisResult): void
    {
        $location = $analysisResult->getLocation();
        $fullDetails = JsonUtils::toArray($analysisResult->getFullDetails());

        Assert::true($this->fileName->isEqual($location->getFileName()));
        $this->analysisResults[] = $analysisResult;

        $lineNumber = $location->getLineNumber()->getLineNumber();
        $lineNumberWidth = strlen((string) $lineNumber);
        if ($lineNumberWidth > $this->maxLineNumberColumnWidth) {
            $this->maxLineNumberColumnWidth = $lineNumberWidth;
        }
        if (!in_array($lineNumber, $this->lineNumbersAffected, true)) {
            $this->lineNumbersAffected[] = $lineNumber;
        }

        $level = ArrayUtils::getStringValue($fullDetails, PhpCodeSnifferFullResultsParser::LEVEL);
        switch ($level) {
            case 'ERROR':
                $this->numberOfErrors++;
                $length = strlen('ERROR');
                if ($length > $this->maxSeverityColumnWidth) {
                    $this->maxSeverityColumnWidth = $length;
                }
                break;

            case 'WARNING':
                $this->numberOfWarnings++;
                $length = strlen('WARNING');
                if ($length > $this->maxSeverityColumnWidth) {
                    $this->maxSeverityColumnWidth = $length;
                }
                break;

            default:
                throw new InvalidFileFormatException("Unknown severity [$level]");
        }

        $message = $analysisResult->getType()->getType();
        $messageWidth = strlen($message);
        if ($messageWidth > $this->maxMessageWidth) {
            $this->maxMessageWidth = $messageWidth;
        }

        if (true === $fullDetails[PhpCodeSnifferFullResultsParser::FIXABLE]) {
            ++$this->fixableIssues;
        }

        $this->absoluteFileNameAsString = ArrayUtils::getStringValue(
            $fullDetails,
            PhpCodeSnifferFullResultsParser::FULL_FILE_NAME
        );
    }

    /**
     * @phpstan-return array<mixed>
     */
    public function asStrings(): array
    {
        $maxWidth = 8 + $this->maxMessageWidth + $this->maxSeverityColumnWidth + $this->maxLineNumberColumnWidth;
        $hasFixableIssues = $this->fixableIssues > 0;

        if ($hasFixableIssues) {
            $maxWidth += 4;
        }

        $lineBreak = str_repeat('-', $maxWidth);

        $summaryLine = 'FOUND ';
        $showAnd = false;

        if ($this->numberOfErrors > 0) {
            $showAnd = true;
            $summaryLine .= $this->asCount($this->numberOfErrors, 'ERROR');
        }

        if ($this->numberOfWarnings > 0) {
            if ($showAnd) {
                $summaryLine .= ' AND ';
            }
            $summaryLine .= $this->asCount($this->numberOfWarnings, 'WARNING');
        }

        $numberOfLinesAffected = count($this->lineNumbersAffected);
        $summaryLine .= ' AFFECTING '.$this->asCount($numberOfLinesAffected, 'LINE');

        Assert::notNull($this->absoluteFileNameAsString);

        $output = [
            'FILE: '.$this->absoluteFileNameAsString,
            $lineBreak,
            $summaryLine,
            $lineBreak,
        ];

        foreach ($this->analysisResults as $analysisResult) {
            $fullDetails = JsonUtils::toArray($analysisResult->getFullDetails());

            $checkbox = $hasFixableIssues ? ' [ ]' : '';
            if (true === $fullDetails[PhpCodeSnifferFullResultsParser::FIXABLE]) {
                $checkbox = ' [x]';
            }

            $level = ArrayUtils::getStringValue($fullDetails, PhpCodeSnifferFullResultsParser::LEVEL);

            $output[] = sprintf(
                " %{$this->maxLineNumberColumnWidth}d | %-{$this->maxSeverityColumnWidth}s |%s %s",
                $analysisResult->getLocation()->getLineNumber()->getLineNumber(),
                $level,
                $checkbox,
                $analysisResult->getType()->getType()
            );
        }

        $output[] = $lineBreak;

        if ($hasFixableIssues) {
            $output[] = "PHPCBF CAN FIX THE {$this->fixableIssues} MARKED SNIFF VIOLATIONS AUTOMATICALLY";
            $output[] = $lineBreak;
        }

        return $output;
    }

    private function asCount(int $count, string $string): string
    {
        return sprintf('%d %s%s',
            $count,
            $string,
            (1 === $count) ? '' : 'S'
        );
    }
}
