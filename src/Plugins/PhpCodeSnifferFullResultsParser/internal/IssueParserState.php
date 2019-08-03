<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\JsonUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;
use DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\PhpCodeSnifferFullResultsParser;

class IssueParserState implements LineParserState
{
    /**
     * @var AnalysisResults
     */
    private $analysisResults;
    /**
     * @var FileName
     */
    private $fileName;
    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    public function __construct(AnalysisResults $analysisResults, FileName $fileName, ProjectRoot $projectRoot)
    {
        $this->analysisResults = $analysisResults;
        $this->fileName = $fileName;
        $this->projectRoot = $projectRoot;
    }

    public function parseLine(string $line): LineParserState
    {
        if (LineDetector::isLine($line)) {
            return new FileNameLineParserState($this->analysisResults, $this->projectRoot);
        }

        $parts = explode('|', $line, 3);
        if (3 !== count($parts)) {
            throw new InvalidFileFormatException('Expecting issue');
        }

        $lineNumberString = trim($parts[0]);
        $lineNumber = (int) $lineNumberString;
        $level = trim($parts[1]);
        $fullIssue = trim($parts[2]);
        $fixable = false;

        if (StringUtils::startsWith('[ ] ', $fullIssue)) {
            $type = StringUtils::removeFromStart('[ ] ', $fullIssue);
        } elseif (StringUtils::startsWith('[x] ', $fullIssue)) {
            $fixable = true;
            $type = StringUtils::removeFromStart('[x] ', $fullIssue);
        } else {
            $type = $fullIssue;
        }

        $fileNameAsString = $this->fileName->getFileName();
        $relativeFileName = $this->projectRoot->getPathRelativeToRootDirectory($fileNameAsString);

        $analysisResult = new AnalysisResult(
            new Location(new FileName($relativeFileName), new LineNumber($lineNumber)),
            new Type($type),
            $type,
            JsonUtils::toString([
                PhpCodeSnifferFullResultsParser::FULL_FILE_NAME => $fileNameAsString,
                PhpCodeSnifferFullResultsParser::LEVEL => $level,
                PhpCodeSnifferFullResultsParser::FIXABLE => $fixable,
            ])
        );

        $this->analysisResults->addAnalysisResult($analysisResult);

        return $this;
    }
}
