<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PhpCodeSnifferFullResultsParser\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\StringUtils;

class FileNameLineParserState implements LineParserState
{
    private const FILE_PREFIX = 'FILE: ';

    /**
     * @var AnalysisResults
     */
    private $analysisResults;
    /**
     * @var ProjectRoot
     */
    private $projectRoot;

    public function __construct(AnalysisResults $analysisResults, ProjectRoot $projectRoot)
    {
        $this->analysisResults = $analysisResults;
        $this->projectRoot = $projectRoot;
    }

    public function parseLine(string $line): LineParserState
    {
        if (StringUtils::startsWith(self::FILE_PREFIX, $line)) {
            $fileNameAsString = StringUtils::removeFromStart(self::FILE_PREFIX, $line);
            $fileName = new FileName($fileNameAsString);

            return new FirstLineParserState($this->analysisResults, $fileName, $this->projectRoot);
        }

        return $this;
    }
}
