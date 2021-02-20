<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Plugins\ResultsParsers;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\ParseAtLocationException;

trait ExpectParseExceptionWithResultTrait
{
    private function expectParseAtLocationExceptionForResult(int $resultWithIssue): void
    {
        $this->expectException(ParseAtLocationException::class);
        $messageContainsRegEx = "/^Issue with result \[$resultWithIssue\]/";
        $this->expectExceptionMessageMatches($messageContainsRegEx);
    }
}
