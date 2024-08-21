<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Helpers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use PHPUnit\Framework\Assert;

trait AssertResultMatch
{
    private function assertMatch(
        AnalysisResult $analysisResult,
        string $fileName,
        int $lineNumber,
        string $type,
        Severity $severity,
    ): void {
        Assert::assertSame($fileName, $analysisResult->getLocation()->getRelativeFileName()->getFileName());
        Assert::assertSame($lineNumber, $analysisResult->getLocation()->getLineNumber()->getLineNumber());
        Assert::assertSame($type, $analysisResult->getType()->getType());
        Assert::assertSame($severity->getSeverity(), $analysisResult->getSeverity()->getSeverity());
    }
}
