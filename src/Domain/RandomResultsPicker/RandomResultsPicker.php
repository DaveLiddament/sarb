<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\RandomResultsPicker;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResults;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\RandomNumberGenerator;

class RandomResultsPicker
{
    private const RANDOM_ISSUES_TO_FIX = 5;

    /**
     * @var RandomNumberGenerator
     */
    private $randomNumberGenerator;

    public function __construct(RandomNumberGenerator $randomNumberGenerator)
    {
        $this->randomNumberGenerator = $randomNumberGenerator;
    }

    /**
     * Returns a random selection of the issues found.
     */
    public function getRandomResultsToFix(AnalysisResults $issues): AnalysisResults
    {
        $randomIssuesToFix = min(self::RANDOM_ISSUES_TO_FIX, $issues->getCount());

        $randomIssues = new AnalysisResultsBuilder();
        $issuesToPickFrom = $issues->getAnalysisResults();

        for ($i = 0; $i < $randomIssuesToFix; ++$i) {
            $totalRemaining = count($issuesToPickFrom);
            $issuePickedIndex = $this->randomNumberGenerator->getRandomNumber($totalRemaining - 1);
            $issuePicked = $issuesToPickFrom[$issuePickedIndex];
            $randomIssues->addAnalysisResult($issuePicked);
            array_splice($issuesToPickFrom, $issuePickedIndex, 1);
        }

        return $randomIssues->build();
    }
}
