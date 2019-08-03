<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\InvalidFileFormatException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

class SummaryLineParserState implements LineParserState
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
        if (StringUtils::startsWith('FOUND ', $line)) {
            return new SecondLineParserState($this->analysisResults, $this->fileName, $this->projectRoot);
        }
        throw new InvalidFileFormatException('Expected summary line');
    }
}
