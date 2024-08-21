<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\RandomResultsPicker;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\LineNumber;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Location;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Severity;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\Type;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\RandomResultsPicker\RandomResultsPicker;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResult;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\AnalysisResultsBuilder;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\RandomNumberGenerator;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class RandomResultsPickerTest extends TestCase
{
    /**
     * @var ProjectRoot
     */
    private $projectRoot;
    /**
     * @var RandomResultsPicker
     */
    private $randomResultsPicker;
    /**
     * @var RandomNumberGenerator&Stub
     */
    private $randomNumberGenerator;

    protected function setUp(): void
    {
        $this->projectRoot = ProjectRoot::fromCurrentWorkingDirectory('/');

        $this->randomNumberGenerator = $this->createStub(RandomNumberGenerator::class);
        $this->randomResultsPicker = new RandomResultsPicker($this->randomNumberGenerator);
    }

    public function testPick5Results(): void
    {
        $issuesBuilder = new AnalysisResultsBuilder();
        $issue1 = $this->addIssue($issuesBuilder, 'a');
        $issue2 = $this->addIssue($issuesBuilder, 'b');
        $this->addIssue($issuesBuilder, 'c');
        $issue4 = $this->addIssue($issuesBuilder, 'd');
        $this->addIssue($issuesBuilder, 'e');
        $issue6 = $this->addIssue($issuesBuilder, 'f');
        $issue7 = $this->addIssue($issuesBuilder, 'g');

        $this->setRandomNumbers(5, 1, 0, 3, 1);

        $pickedIssues = $this->randomResultsPicker->getRandomResultsToFix($issuesBuilder->build());

        $expected = [
            $issue1,
            $issue2,
            $issue4,
            $issue6,
            $issue7,
        ];

        $this->assertEquals($expected, $pickedIssues->getAnalysisResults());
        $this->assertSame(5, $pickedIssues->getCount());
        $this->assertFalse($pickedIssues->hasNoIssues());
    }

    public function testPickWhenFewerThan5Results(): void
    {
        $issuesBuilder = new AnalysisResultsBuilder();
        $issue1 = $this->addIssue($issuesBuilder, 'a');
        $issue2 = $this->addIssue($issuesBuilder, 'b');
        $issue3 = $this->addIssue($issuesBuilder, 'c');

        $this->setRandomNumbers(2, 0, 0);

        $pickedIssues = $this->randomResultsPicker->getRandomResultsToFix($issuesBuilder->build());

        $expected = [
            $issue1,
            $issue2,
            $issue3,
        ];

        $this->assertEquals($expected, $pickedIssues->getAnalysisResults());
        $this->assertSame(3, $pickedIssues->getCount());
        $this->assertFalse($pickedIssues->hasNoIssues());
    }

    private function addIssue(AnalysisResultsBuilder $issuesBuilder, string $string): AnalysisResult
    {
        $analysisResult = new AnalysisResult(
            Location::fromRelativeFileName(
                new RelativeFileName($string),
                $this->projectRoot,
                new LineNumber(10),
            ),
            new Type("Type-{$string}"),
            $string,
            [],
            Severity::error(),
        );

        $issuesBuilder->addAnalysisResult($analysisResult);

        return $analysisResult;
    }

    private function setRandomNumbers(int ...$numbers): void
    {
        $this->randomNumberGenerator->method('getRandomNumber')->willReturnOnConsecutiveCalls(...$numbers);
    }
}
